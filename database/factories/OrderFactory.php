<?php

namespace Database\Factories;

use App\Models\Currency;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'number' => 'ORD-'.strtoupper(Str::random(8)),
            'status' => 'pending',
            'total' => fake()->randomFloat(2, 10, 500),
            'currency_id' => Currency::factory(),
        ];
    }
}
