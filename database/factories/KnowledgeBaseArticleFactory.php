<?php

namespace Database\Factories;

use App\Models\KnowledgeBaseArticle;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class KnowledgeBaseArticleFactory extends Factory
{
    protected $model = KnowledgeBaseArticle::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'slug' => Str::slug(fake()->unique()->sentence()),
            'content' => fake()->paragraphs(5, true),
            'category' => fake()->randomElement(['Getting Started', 'Billing', 'Technical', 'FAQ']),
            'is_published' => true,
            'order' => fake()->numberBetween(0, 100),
        ];
    }
}
