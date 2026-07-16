<?php

namespace Database\Factories;

use App\Models\TicketThread;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TicketThreadFactory extends Factory
{
    protected $model = TicketThread::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'number' => 'TKT-'.strtoupper(Str::random(8)),
            'subject' => fake()->sentence(),
            'status' => 'open',
            'priority' => fake()->randomElement(['low', 'medium', 'high', 'urgent']),
        ];
    }
}
