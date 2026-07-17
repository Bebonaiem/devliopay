<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductPricing;
use App\Models\Service;
use App\Notifications\InvoiceCreated;
use App\Services\BillingService;
use App\Services\NotificationService;
use App\Services\PromoCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function index()
    {
        $cart = session()->get('cart', []);
        $items = [];
        $subtotal = 0;

        foreach ($cart as $key => $item) {
            $product = Product::with('pricing.currencies')->find($item['product_id']);
            $pricing = ProductPricing::with('currencies')->find($item['pricing_id']);

            if ($product && $pricing) {
                $price = $pricing->currencies->first()?->pivot->amount ?? 0;
                $setupFee = $pricing->currencies->first()?->pivot->setup_fee ?? 0;
                $qty = $item['quantity'] ?? 1;
                $items[$key] = [
                    'product' => $product,
                    'pricing' => $pricing,
                    'price' => $price,
                    'setup_fee' => $setupFee,
                    'quantity' => $qty,
                    'line_total' => ($price * $qty) + $setupFee,
                ];
                $subtotal += ($price * $qty) + $setupFee;
            }
        }

        $user = Auth::user();
        $taxRate = \App\Models\TaxRate::findByLocation($user?->country, $user?->state, $user?->zip_code);
        if ($taxRate && !$taxRate->is_inclusive) {
            $tax = $taxRate->calculateTax($subtotal);
        } else {
            $tax = 0;
        }
        $total = $subtotal + $tax;

        $promoDiscount = session()->get('promo_discount', 0);
        $promoCode = session()->get('promo_code', null);
        if ($promoDiscount > 0 && $total > $promoDiscount) {
            $total -= $promoDiscount;
        }

        return view('cart.index', compact('items', 'subtotal', 'tax', 'total', 'promoDiscount', 'promoCode'));
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'pricing_id' => 'required|exists:product_pricing,id',
        ]);

        $cart = session()->get('cart', []);
        $key = $request->product_id.'-'.$request->pricing_id;
        $product = Product::find($request->product_id);
        $quantity = max(1, (int) ($request->quantity ?? 1));

        if (isset($cart[$key])) {
            if ($product && $product->allow_quantity === 'no') {
                return redirect()->route('cart.index')
                    ->with('error', 'This item is already in your cart.');
            }

            if ($product && $product->allow_quantity === 'separated') {
                $cart[$key]['quantity'] = $quantity;
            } else {
                $cart[$key]['quantity'] += $quantity;
            }
        } else {
            $cart[$key] = [
                'product_id' => $request->product_id,
                'pricing_id' => $request->pricing_id,
                'quantity' => $quantity,
                'config' => $request->only(['hostname', 'os_type', 'server_name']),
            ];
        }

        session()->put('cart', $cart);

        return redirect()->route('cart.index')
            ->with('success', 'Item added to cart');
    }

    public function remove(string $key)
    {
        $cart = session()->get('cart', []);
        unset($cart[$key]);
        session()->put('cart', $cart);

        return redirect()->route('cart.index')
            ->with('success', 'Item removed from cart');
    }

    public function updateQuantity(Request $request, string $key)
    {
        $cart = session()->get('cart', []);

        if (!isset($cart[$key])) {
            return redirect()->route('cart.index')->with('error', 'Item not found in cart.');
        }

        $item = $cart[$key];
        $product = Product::find($item['product_id']);

        if (!$product || $product->allow_quantity === 'no') {
            return redirect()->route('cart.index')->with('error', 'Quantity cannot be changed for this item.');
        }

        $action = $request->input('action');
        $currentQty = $item['quantity'] ?? 1;

        if ($action === 'increase') {
            $cart[$key]['quantity'] = $currentQty + 1;
        } elseif ($action === 'decrease' && $currentQty > 1) {
            $cart[$key]['quantity'] = $currentQty - 1;
        }

        session()->put('cart', $cart);

        return redirect()->route('cart.index');
    }

    public function clear()
    {
        session()->forget('cart');
        session()->forget('promo_code');
        session()->forget('promo_discount');

        return redirect()->route('cart.index')
            ->with('success', 'Cart cleared');
    }

    public function checkout()
    {
        $user = Auth::user();
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $currency = Currency::where('is_default', true)->first();

        $total = 0;
        $orderItems = [];

        foreach ($cart as $key => $item) {
            $pricing = ProductPricing::with('currencies', 'product')->find($item['pricing_id']);
            if (! $pricing) {
                continue;
            }

            $price = $pricing->currencies->first()?->pivot->amount ?? 0;
            $product = $pricing->product;
            $setupFee = $pricing->currencies->first()?->pivot->setup_fee ?? 0;

            $orderItems[] = [
                'product_id' => $item['product_id'],
                'pricing_id' => $item['pricing_id'],
                'quantity' => $item['quantity'] ?? 1,
                'price' => $price,
                'setup_fee' => $setupFee,
                'config_options' => $item['config'] ?? null,
                'product' => $product,
                'pricing' => $pricing,
            ];

            $total += ($price + $setupFee) * ($item['quantity'] ?? 1);
        }

        if (empty($orderItems)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $taxRate = \App\Models\TaxRate::findByLocation($user->country, $user->state, $user->zip_code);
        $taxAmount = 0;
        if ($taxRate && ! $taxRate->is_inclusive) {
            $taxAmount = $taxRate->calculateTax($total);
        }

        $subtotal = $total;
        $totalWithTax = $total + $taxAmount;

        // Apply promo discount
        $promoDiscount = session()->get('promo_discount', 0);
        $promoCode = session()->get('promo_code');
        if ($promoDiscount > 0 && $totalWithTax > $promoDiscount) {
            $totalWithTax -= $promoDiscount;
        }

        $order = null;
        $invoice = null;
        $serviceIds = [];

        DB::transaction(function () use ($user, $totalWithTax, $subtotal, $taxAmount, $taxRate, $currency, $orderItems, $promoCode, &$order, &$invoice, &$serviceIds) {
            $order = Order::create([
                'user_id' => $user->id,
                'status' => 'pending',
                'subtotal' => $subtotal,
                'tax' => $taxAmount,
                'total' => $totalWithTax,
                'currency_id' => $currency?->id,
            ]);

            foreach ($orderItems as $itemData) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $itemData['product_id'],
                    'pricing_id' => $itemData['pricing_id'],
                    'quantity' => $itemData['quantity'],
                    'price' => $itemData['price'],
                    'setup_fee' => $itemData['setup_fee'],
                    'config_options' => $itemData['config_options'],
                ]);
            }

            $billingService = new BillingService;
            $invoice = $billingService->generateOrderInvoice($order);

            app(NotificationService::class)->notify($user, new InvoiceCreated($invoice));

            $order->load('items.product', 'items.pricing');
            foreach ($order->items as $item) {
                $product = $item->product;
                $qty = $item->quantity ?? 1;
                for ($i = 0; $i < $qty; $i++) {
                    $service = Service::create([
                        'user_id' => $user->id,
                        'product_id' => $item->product_id,
                        'pricing_id' => $item->pricing_id,
                        'order_id' => $order->id,
                        'status' => 'pending',
                        'config_options' => $item->config_options,
                        'server_extension' => $product?->server_extension ?? null,
                    ]);
                    $serviceIds[] = $service->id;
                }
            }

            if (! empty($serviceIds) && ! $invoice->service_id) {
                $invoice->update(['service_id' => $serviceIds[0]]);
            }

            // Apply promo code usage
            if ($promoCode) {
                $promoService = new PromoCodeService;
                $promoService->apply($promoCode);
            }
        });

        // Clear session AFTER transaction succeeds
        session()->forget('cart');
        session()->forget('promo_code');
        session()->forget('promo_discount');

        return redirect()->route('client.invoices.show', $invoice)
            ->with('success', 'Order placed successfully. Please complete your payment.');
    }

    public function applyPromo(Request $request)
    {
        $request->validate([
            'promo_code' => 'required|string',
        ]);

        $cart = session()->get('cart', []);
        $total = 0;
        $productIds = [];

        foreach ($cart as $item) {
            $pricing = ProductPricing::with('currencies')->find($item['pricing_id']);
            if (! $pricing) {
                continue;
            }
            $price = $pricing->currencies->first()?->pivot->amount ?? 0;
            $total += $price * ($item['quantity'] ?? 1);
            $productIds[] = $item['product_id'];
        }

        $promoService = new PromoCodeService;
        $result = $promoService->validate($request->promo_code, $total, $productIds);

        if ($result['valid']) {
            session()->put('promo_code', $result['code']);
            session()->put('promo_discount', $result['discount']);

            return redirect()->route('cart.index')
                ->with('success', 'Promo code applied! You save '.Currency::defaultSymbol().number_format($result['discount'], 2));
        }

        return redirect()->route('cart.index')
            ->with('error', $result['error']);
    }

    public function removePromo()
    {
        session()->forget('promo_code');
        session()->forget('promo_discount');

        return redirect()->route('cart.index')
            ->with('success', 'Promo code removed');
    }
}
