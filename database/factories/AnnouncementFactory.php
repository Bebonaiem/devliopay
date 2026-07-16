<?php

namespace Database\Factories;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AnnouncementFactory extends Factory
{
    protected $model = Announcement::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(),
            'slug' => Str::slug(fake()->unique()->sentence()),
            'content' => fake()->paragraphs(3, true),
            'is_published' => true,
            'is_sticky' => false,
            'published_at' => now(),
        ];
    }
}
