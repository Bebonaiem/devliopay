<?php

namespace Database\Factories;

use App\Models\PromoCode;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PromoCodeFactory extends Factory
{
    protected $model = PromoCode::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper(Str::random(8)),
            'type' => fake()->randomElement(['percentage', 'fixed']),
            'value' => fake()->randomElement([10, 15, 20, 25, 50]),
            'max_uses' => 100,
            'used_count' => 0,
            'is_active' => true,
            'starts_at' => now(),
            'expires_at' => now()->addMonths(3),
        ];
    }
}
