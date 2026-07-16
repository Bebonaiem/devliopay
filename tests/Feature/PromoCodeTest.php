<?php

namespace Tests\Feature;

use App\Models\PromoCode;
use App\Services\PromoCodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromoCodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_validates_active_percentage_code(): void
    {
        $promo = PromoCode::factory()->create([
            'type' => 'percentage',
            'value' => 20,
            'is_active' => true,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
        ]);

        $service = new PromoCodeService;
        $result = $service->validate($promo->code, 100.00);

        $this->assertTrue($result['valid']);
        $this->assertEquals(20, $result['discount']);
        $this->assertEquals('percentage', $result['type']);
    }

    public function test_rejects_expired_code(): void
    {
        $promo = PromoCode::factory()->create([
            'is_active' => true,
            'starts_at' => now()->subMonths(3),
            'expires_at' => now()->subDay(),
        ]);

        $service = new PromoCodeService;
        $result = $service->validate($promo->code, 100.00);

        $this->assertFalse($result['valid']);
    }

    public function test_rejects_inactive_code(): void
    {
        $promo = PromoCode::factory()->create([
            'is_active' => false,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
        ]);

        $service = new PromoCodeService;
        $result = $service->validate($promo->code, 100.00);

        $this->assertFalse($result['valid']);
    }

    public function test_rejects_code_not_yet_started(): void
    {
        $promo = PromoCode::factory()->create([
            'is_active' => true,
            'starts_at' => now()->addDay(),
            'expires_at' => now()->addMonth(),
        ]);

        $service = new PromoCodeService;
        $result = $service->validate($promo->code, 100.00);

        $this->assertFalse($result['valid']);
    }

    public function test_rejects_used_up_code(): void
    {
        $promo = PromoCode::factory()->create([
            'is_active' => true,
            'max_uses' => 10,
            'used_count' => 10,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
        ]);

        $service = new PromoCodeService;
        $result = $service->validate($promo->code, 100.00);

        $this->assertFalse($result['valid']);
    }

    public function test_rejects_below_minimum_amount(): void
    {
        $promo = PromoCode::factory()->create([
            'is_active' => true,
            'min_amount' => 50.00,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
        ]);

        $service = new PromoCodeService;
        $result = $service->validate($promo->code, 25.00);

        $this->assertFalse($result['valid']);
    }

    public function test_calculates_percentage_discount(): void
    {
        $promo = PromoCode::factory()->create(['type' => 'percentage', 'value' => 25]);

        $discount = $promo->calculateDiscount(100.00);
        $this->assertEquals(25.00, $discount);
    }

    public function test_calculates_fixed_discount(): void
    {
        $promo = PromoCode::factory()->create(['type' => 'fixed', 'value' => 15]);

        $discount = $promo->calculateDiscount(100.00);
        $this->assertEquals(15.00, $discount);
    }

    public function test_discount_does_not_exceed_amount(): void
    {
        $promo = PromoCode::factory()->create(['type' => 'fixed', 'value' => 200]);

        $discount = $promo->calculateDiscount(50.00);
        $this->assertEquals(50.00, $discount);
    }

    public function test_increments_used_count(): void
    {
        $promo = PromoCode::factory()->create(['used_count' => 0]);

        $promo->apply();
        $this->assertEquals(1, $promo->fresh()->used_count);
    }
}
