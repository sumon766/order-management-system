<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_create_order()
    {
        $user = User::factory()->create(['role' => 'customer']);
        $product = Product::factory()->create(['stock_quantity' => 10]);

        $response = $this->actingAs($user)->postJson('/api/v1/orders', [
            'shipping_address' => '123 Test Street',
            'billing_address' => '123 Test Street',
            'customer_email' => 'test@example.com',
            'customer_phone' => '1234567890',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ]
            ]
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['id', 'order_number', 'total_amount']);
    }

    public function test_customer_can_view_own_orders()
    {
        $user = User::factory()->create(['role' => 'customer']);
        Order::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->getJson('/api/v1/orders');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_customer_cannot_view_other_orders()
    {
        $user1 = User::factory()->create(['role' => 'customer']);
        $user2 = User::factory()->create(['role' => 'customer']);
        $order = Order::factory()->create(['user_id' => $user1->id]);

        $response = $this->actingAs($user2)->getJson("/api/v1/orders/{$order->id}");

        $response->assertStatus(403);
    }

    public function test_admin_can_view_all_orders()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Order::factory()->count(3)->create();

        $response = $this->actingAs($admin)->getJson('/api/v1/orders');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_customer_can_cancel_own_order()
    {
        $user = User::factory()->create(['role' => 'customer']);
        $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'pending']);

        $response = $this->actingAs($user)->postJson("/api/v1/orders/{$order->id}/cancel");

        $response->assertStatus(200)
            ->assertJson(['status' => 'cancelled']);
    }
}
