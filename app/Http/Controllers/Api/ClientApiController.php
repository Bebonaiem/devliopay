<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Service;
use Illuminate\Http\Request;

class ClientApiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function dashboard(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'stats' => [
                'active_services' => $user->services()->where('status', 'active')->count(),
                'pending_invoices' => $user->invoices()->where('status', 'pending')->count(),
                'open_tickets' => $user->tickets()->where('status', 'open')->count(),
                'total_spent' => $user->transactions()->where('status', 'completed')->sum('amount'),
            ],
        ]);
    }

    public function services(Request $request)
    {
        $services = $request->user()->services()
            ->with(['product', 'pricing'])
            ->get();

        return response()->json(['data' => $services]);
    }

    public function service(Request $request, Service $service)
    {
        if ($service->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $service->load(['product', 'pricing', 'order']);

        return response()->json(['data' => $service]);
    }

    public function invoices(Request $request)
    {
        $invoices = $request->user()->invoices()
            ->with('items')
            ->get();

        return response()->json(['data' => $invoices]);
    }

    public function invoice(Request $request, Invoice $invoice)
    {
        if ($invoice->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $invoice->load(['items', 'transactions', 'currency']);

        return response()->json(['data' => $invoice]);
    }

    public function tickets(Request $request)
    {
        $tickets = $request->user()->tickets()
            ->with('messages')
            ->get();

        return response()->json(['data' => $tickets]);
    }

    public function products(Request $request)
    {
        $products = Product::where('is_active', true)
            ->where('is_hidden', false)
            ->with(['category', 'pricing.currencies'])
            ->get();

        return response()->json(['data' => $products]);
    }
}
