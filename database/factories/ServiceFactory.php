<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductPricing;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'user_id' => User::factory(),
            'product_id' => Product::factory(),
            'pricing_id' => ProductPricing::factory(),
            'status' => fake()->randomElement(['pending', 'active', 'suspended']),
            'next_billing_at' => now()->addMonth(),
        ];
    }
}
