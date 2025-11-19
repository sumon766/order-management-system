<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(private ProductService $productService) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'low_stock']);

        if (auth()->user()->isVendor()) {
            $filters['user_id'] = auth()->id();
        }

        $products = $this->productService->getAllProducts($filters);

        return response()->json($products);
    }

    public function store(ProductRequest $request): JsonResponse
    {
        if (auth()->user()->isCustomer()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $data = $request->validated();
        $data['user_id'] = auth()->id();

        $product = $this->productService->createProduct($data);

        return response()->json($product, 201);
    }

    public function update(ProductRequest $request, Product $product): JsonResponse
    {
        if (auth()->user()->isCustomer()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (auth()->user()->isVendor() && $product->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $data = $request->validated();
        $product = $this->productService->updateProduct($product, $data);

        return response()->json($product);
    }

    public function destroy(Product $product): JsonResponse
    {
        if (auth()->user()->isCustomer()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (auth()->user()->isVendor() && $product->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $this->productService->deleteProduct($product);

        return response()->json(null, 204);
    }

    public function lowStock(): JsonResponse
    {
        if (auth()->user()->isCustomer()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $products = $this->productService->getLowStockProducts();

        return response()->json($products);
    }
}
