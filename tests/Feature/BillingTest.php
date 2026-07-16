<?php

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductPricing;
use App\Models\Service;
use App\Models\User;
use App\Services\BillingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingTest extends TestCase
{
    use RefreshDatabase;

    private BillingService $billingService;

    private User $user;

    private Service $service;

    private Currency $currency;

    protected function setUp(): void
    {
        parent::setUp();

        $this->billingService = new BillingService;
        $this->user = User::factory()->create();
        $this->currency = Currency::factory()->create(['is_default' => true]);

        $product = Product::factory()->create();
        $pricing = ProductPricing::factory()->create([
            'product_id' => $product->id,
            'type' => 'recurring',
            'interval' => 'month',
            'billing_period' => 1,
        ]);

        $pricing->currencies()->attach($this->currency->id, ['amount' => 29.99, 'setup_fee' => 0]);

        $this->service = Service::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $product->id,
            'pricing_id' => $pricing->id,
            'status' => 'active',
            'next_billing_at' => now()->subDay(),
        ]);
    }

    public function test_generates_invoice_for_service(): void
    {
        $invoice = $this->billingService->generateInvoice($this->service);

        $this->assertNotNull($invoice);
        $this->assertEquals($this->service->user_id, $invoice->user_id);
        $this->assertEquals('pending', $invoice->status);
        $this->assertEquals(29.99, $invoice->total);
    }

    public function test_processes_renewals(): void
    {
        $this->billingService->processRenewals();

        $this->assertDatabaseHas('invoices', [
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);
    }

    public function test_generates_order_invoice(): void
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'currency_id' => $this->currency->id,
            'total' => 99.99,
        ]);

        $invoice = $this->billingService->generateOrderInvoice($order);

        $this->assertNotNull($invoice);
        $this->assertEquals('pending', $invoice->status);
        $this->assertEquals(99.99, $invoice->total);
    }
}
