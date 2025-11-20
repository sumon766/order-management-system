<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendor_can_create_product()
    {
        $user = User::factory()->create(['role' => 'vendor']);

        $response = $this->actingAs($user)->postJson('/api/v1/products', [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'sku' => 'TEST-SKU-001',
            'price' => 99.99,
            'stock_quantity' => 50,
            'low_stock_threshold' => 10,
            'is_active' => true,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['id', 'name', 'sku', 'price']);
    }

    public function test_customer_cannot_create_product()
    {
        $user = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($user)->postJson('/api/v1/products', [
            'name' => 'Test Product',
            'sku' => 'TEST-SKU-001',
            'price' => 99.99,
            'stock_quantity' => 50,
            'low_stock_threshold' => 10,
        ]);

        $response->assertStatus(403);
    }

    public function test_vendor_can_view_own_products()
    {
        $user = User::factory()->create(['role' => 'vendor']);
        Product::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->getJson('/api/v1/products');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_admin_can_view_all_products()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Product::factory()->count(3)->create();

        $response = $this->actingAs($admin)->getJson('/api/v1/products');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }
}
