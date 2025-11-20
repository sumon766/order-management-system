<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $vendors = User::where('role', 'vendor')->get();

        foreach ($vendors as $vendor) {
            Product::factory(8)->create(['user_id' => $vendor->id])
                ->each(function ($product) {
                    ProductVariant::factory(rand(1, 3))->create([
                        'product_id' => $product->id,
                        'price' => $product->price + rand(-10, 20),
                    ]);
                });
        }
    }
}
