<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_page_loads(): void
    {
        Product::factory()->create(['is_active' => true, 'is_hidden' => false]);
        Category::factory()->create(['is_active' => true]);

        $response = $this->get('/store');

        $response->assertStatus(200);
    }

    public function test_store_shows_only_active_products(): void
    {
        Product::factory()->count(3)->create(['is_active' => true, 'is_hidden' => false]);
        Product::factory()->create(['is_active' => false, 'is_hidden' => false]);

        $response = $this->get('/store');

        $response->assertStatus(200);
    }

    public function test_product_page_loads(): void
    {
        $product = Product::factory()->create([
            'is_active' => true,
            'is_hidden' => false,
            'slug' => 'test-product',
        ]);

        $response = $this->get('/store/test-product');

        $response->assertStatus(200);
    }

    public function test_home_page_loads(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
