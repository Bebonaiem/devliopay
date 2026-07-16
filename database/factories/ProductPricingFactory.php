<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductPricing;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductPricingFactory extends Factory
{
    protected $model = ProductPricing::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'name' => fake()->randomElement(['Basic', 'Standard', 'Premium', 'Enterprise']),
            'type' => 'recurring',
            'interval' => fake()->randomElement(['month', 'year']),
            'billing_period' => 1,
        ];
    }
}
