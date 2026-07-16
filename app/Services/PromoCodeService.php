<?php

namespace App\Services;

use App\Models\PromoCode;

class PromoCodeService
{
    public function validate(string $code, float $cartTotal, array $productIds = []): array
    {
        $promo = PromoCode::where('code', strtoupper($code))->first();

        if (! $promo) {
            return ['valid' => false, 'error' => 'Invalid promo code'];
        }

        if (! $promo->isValid()) {
            return ['valid' => false, 'error' => 'This promo code is no longer valid'];
        }

        if ($cartTotal < $promo->min_amount) {
            return [
                'valid' => false,
                'error' => 'Minimum order amount is $'.number_format($promo->min_amount, 2),
            ];
        }

        if ($promo->applicable_products && ! empty($promo->applicable_products)) {
            $applicable = array_intersect($productIds, $promo->applicable_products);
            if (empty($applicable)) {
                return ['valid' => false, 'error' => 'This promo code is not applicable to your cart'];
            }
        }

        $discount = $promo->calculateDiscount($cartTotal);

        return [
            'valid' => true,
            'code' => $promo->code,
            'type' => $promo->type,
            'value' => $promo->value,
            'discount' => $discount,
        ];
    }

    public function apply(string $code): void
    {
        $promo = PromoCode::where('code', strtoupper($code))->first();
        if ($promo) {
            $promo->apply();
        }
    }
}
