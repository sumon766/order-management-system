<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductRepository
{
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = Product::with(['user', 'variants']);

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('sku', 'like', '%' . $filters['search'] . '%');
        }

        if (isset($filters['low_stock'])) {
            $query->whereRaw('stock_quantity <= low_stock_threshold');
        }

        return $query->paginate(15);
    }

    public function findById(int $id): ?Product
    {
        return Product::with(['user', 'variants'])->find($id);
    }

    public function create(array $data): Product
    {
        return Product::create($data);
    }

    public function update(Product $product, array $data): bool
    {
        return $product->update($data);
    }

    public function delete(Product $product): bool
    {
        return $product->delete();
    }

    public function getLowStockProducts(): LengthAwarePaginator
    {
        return Product::whereRaw('stock_quantity <= low_stock_threshold')
                     ->where('is_active', true)
                     ->paginate(15);
    }
}
