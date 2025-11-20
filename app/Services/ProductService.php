<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\ProductRepository;
use App\Repositories\ProductVariantRepository;
use Illuminate\Support\Facades\DB;

class ProductService
{
    public function __construct(
        private ProductRepository $productRepository,
        private ProductVariantRepository $variantRepository
    ) {}

    public function getAllProducts(array $filters = [])
    {
        return $this->productRepository->getAll($filters);
    }

    public function createProduct(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            $product = $this->productRepository->create($data);

            if (isset($data['variants'])) {
                foreach ($data['variants'] as $variantData) {
                    $this->variantRepository->create(array_merge($variantData, [
                        'product_id' => $product->id
                    ]));
                }
            }

            return $product->load('variants');
        });
    }

    public function updateProduct(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data) {
            $this->productRepository->update($product, $data);

            if (isset($data['variants'])) {
                foreach ($data['variants'] as $variantData) {
                    if (isset($variantData['id'])) {
                        $variant = $product->variants()->find($variantData['id']);
                        if ($variant) {
                            $this->variantRepository->update($variant, $variantData);
                        }
                    } else {
                        $this->variantRepository->create(array_merge($variantData, [
                            'product_id' => $product->id
                        ]));
                    }
                }
            }

            return $product->fresh('variants');
        });
    }

    public function deleteProduct(Product $product): bool
    {
        return DB::transaction(function () use ($product) {
            $product->variants()->delete();
            return $this->productRepository->delete($product);
        });
    }

    public function updateStock(Product $product, int $quantity): bool
    {
        $result = $this->productRepository->update($product, [
            'stock_quantity' => $quantity
        ]);

        if ($product->isLowStock()) {
            LowStockAlert::dispatch($product);
        }

        return $result;
    }

    public function getLowStockProducts()
    {
        return $this->productRepository->getLowStockProducts();
    }
}
