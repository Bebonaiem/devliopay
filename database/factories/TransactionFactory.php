<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'invoice_id' => Invoice::factory(),
            'status' => 'completed',
            'amount' => fake()->randomFloat(2, 10, 500),
            'gateway' => fake()->randomElement(['stripe', 'paypal']),
            'gateway_id' => 'ch_'.Str::random(16),
            'completed_at' => now(),
        ];
    }
}
