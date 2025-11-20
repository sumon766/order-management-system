<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function checkStock(Product $product, ?ProductVariant $variant = null, int $quantity): bool
    {
        if ($variant) {
            return $variant->stock_quantity >= $quantity;
        }

        return $product->stock_quantity >= $quantity;
    }

    public function deductStock(Product $product, ?ProductVariant $variant = null, int $quantity): void
    {
        DB::transaction(function () use ($product, $variant, $quantity) {
            if ($variant) {
                $variant->decrement('stock_quantity', $quantity);
            } else {
                $product->decrement('stock_quantity', $quantity);
            }
        });
    }

    public function restoreStock(Product $product, ?ProductVariant $variant = null, int $quantity): void
    {
        DB::transaction(function () use ($product, $variant, $quantity) {
            if ($variant) {
                $variant->increment('stock_quantity', $quantity);
            } else {
                $product->increment('stock_quantity', $quantity);
            }
        });
    }
}
