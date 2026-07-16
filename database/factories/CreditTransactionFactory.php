<?php

namespace Database\Factories;

use App\Models\CreditTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CreditTransactionFactory extends Factory
{
    protected $model = CreditTransaction::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'amount' => fake()->randomFloat(2, 5, 200),
            'type' => fake()->randomElement(['deposit', 'withdrawal', 'adjustment']),
            'description' => fake()->sentence(),
        ];
    }
}
