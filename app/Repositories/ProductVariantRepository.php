<?php

namespace App\Repositories;

use App\Models\ProductVariant;

class ProductVariantRepository
{
    public function create(array $data): ProductVariant
    {
        return ProductVariant::create($data);
    }

    public function update(ProductVariant $variant, array $data): bool
    {
        return $variant->update($data);
    }

    public function delete(ProductVariant $variant): bool
    {
        return $variant->delete();
    }

    public function updateStock(ProductVariant $variant, int $quantity): bool
    {
        return $variant->update(['stock_quantity' => $quantity]);
    }

    public function findBySku(string $sku): ?ProductVariant
    {
        return ProductVariant::where('sku', $sku)->first();
    }
}
