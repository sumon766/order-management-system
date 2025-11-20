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
    /**
     * @OA\Tag(
     *     name="Products",
     *     description="Product Management Endpoints"
     * )
     */
    public function __construct(private ProductService $productService) {}

    /**
     * @OA\Get(
     *     path="/api/v1/products",
     *     summary="Get all products",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search products by name or SKU",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="low_stock",
     *         in="query",
     *         description="Filter low stock products",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Products retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Product")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'low_stock']);

        if (auth()->user()->isVendor()) {
            $filters['user_id'] = auth()->id();
        }

        $products = $this->productService->getAllProducts($filters);

        return response()->json($products);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/products",
     *     summary="Create a new product",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","sku","price","stock_quantity","low_stock_threshold"},
     *             @OA\Property(property="name", type="string", example="iPhone 15 Pro"),
     *             @OA\Property(property="description", type="string", example="Latest Apple smartphone with advanced features"),
     *             @OA\Property(property="sku", type="string", example="IPHONE-15-PRO-256"),
     *             @OA\Property(property="price", type="number", format="float", example=1199.99),
     *             @OA\Property(property="stock_quantity", type="integer", example=100),
     *             @OA\Property(property="low_stock_threshold", type="integer", example=10),
     *             @OA\Property(property="is_active", type="boolean", example=true),
     *             @OA\Property(
     *                 property="variants",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="size", type="string", example="256GB"),
     *                     @OA\Property(property="color", type="string", example="Titanium Black"),
     *                     @OA\Property(property="material", type="string", example="Titanium"),
     *                     @OA\Property(property="sku", type="string", example="IPHONE-15-PRO-256-BLK"),
     *                     @OA\Property(property="price", type="number", format="float", example=1199.99),
     *                     @OA\Property(property="stock_quantity", type="integer", example=50)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Product")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized - Vendor or Admin role required"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/v1/products/{id}",
     *     summary="Get a specific product",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Product ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Product")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found"
     *     )
     * )
     */
    public function show(Product $product): JsonResponse
    {
        if (auth()->user()->isVendor() && $product->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json($product->load('variants'));
    }

    /**
     * @OA\Put(
     *     path="/api/v1/products/{id}",
     *     summary="Update a product",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Product ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","sku","price","stock_quantity","low_stock_threshold"},
     *             @OA\Property(property="name", type="string", example="iPhone 15 Pro Updated"),
     *             @OA\Property(property="description", type="string", example="Updated description"),
     *             @OA\Property(property="sku", type="string", example="IPHONE-15-PRO-256-UPD"),
     *             @OA\Property(property="price", type="number", format="float", example=1099.99),
     *             @OA\Property(property="stock_quantity", type="integer", example=80),
     *             @OA\Property(property="low_stock_threshold", type="integer", example=15),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Product")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
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

    /**
     * @OA\Delete(
     *     path="/api/v1/products/{id}",
     *     summary="Delete a product",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Product ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Product deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found"
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/v1/products/low-stock/alerts",
     *     summary="Get low stock products",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Low stock products retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Product")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized - Vendor or Admin role required"
     *     )
     * )
     */
    public function lowStock(): JsonResponse
    {
        if (auth()->user()->isCustomer()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $products = $this->productService->getLowStockProducts();

        return response()->json($products);
    }
}
