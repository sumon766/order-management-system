<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::where('role', 'customer')->get();
        $products = Product::all();

        foreach ($customers as $customer) {
            Order::factory(3)->create(['user_id' => $customer->id])
                ->each(function ($order) use ($products) {
                    $orderItems = $products->random(rand(1, 4));

                    foreach ($orderItems as $product) {
                        OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => $product->id,
                            'product_variant_id' => $product->variants->random()->id ?? null,
                            'product_name' => $product->name,
                            'sku' => $product->sku,
                            'unit_price' => $product->price,
                            'quantity' => rand(1, 3),
                            'total_price' => $product->price * rand(1, 3),
                        ]);
                    }

                    $order->update([
                        'total_amount' => $order->items->sum('total_price')
                    ]);
                });
        }
    }
}
