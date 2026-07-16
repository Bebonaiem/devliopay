<?php

namespace Database\Factories;

use App\Models\Currency;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'number' => 'INV-'.strtoupper(Str::random(8)),
            'status' => fake()->randomElement(['pending', 'paid', 'overdue']),
            'subtotal' => fake()->randomFloat(2, 10, 500),
            'tax' => 0,
            'total' => 0,
            'currency_id' => Currency::factory(),
            'due_at' => now()->addDays(7),
        ];
    }
}
