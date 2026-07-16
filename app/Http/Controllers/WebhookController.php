<?php

namespace App\Http\Controllers;

use App\Services\Gateways\PayPalGateway;
use App\Services\Gateways\StripeGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function stripe(Request $request)
    {
        try {
            $payload = $request->getContent();
            $sigHeader = $request->header('Stripe-Signature');

            if (empty($sigHeader)) {
                Log::warning('Stripe webhook missing signature header');

                return response()->json(['error' => 'Missing signature'], 400);
            }

            $gateway = new StripeGateway;

            if (! $gateway->verifyWebhookSignature($payload, $sigHeader)) {
                Log::warning('Stripe webhook signature verification failed');

                return response()->json(['error' => 'Invalid signature'], 400);
            }

            $data = json_decode($payload, true);
            if (! $data) {
                return response()->json(['error' => 'Invalid payload'], 400);
            }

            $gateway->handleWebhook($data);

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('Stripe webhook error: '.$e->getMessage());

            return response()->json(['error' => 'Webhook error'], 400);
        }
    }

    public function paypal(Request $request)
    {
        try {
            $payload = $request->all();

            if (empty($payload['event_type'])) {
                return response()->json(['error' => 'Invalid payload'], 400);
            }

            $gateway = new PayPalGateway;

            if (! $gateway->verifyWebhookSignature($request)) {
                Log::warning('PayPal webhook signature verification failed');

                return response()->json(['error' => 'Invalid signature'], 400);
            }

            $gateway->handleWebhook($payload);

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('PayPal webhook error: '.$e->getMessage());

            return response()->json(['error' => 'Webhook error'], 400);
        }
    }
}
