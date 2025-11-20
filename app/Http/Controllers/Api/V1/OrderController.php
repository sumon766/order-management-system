<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Orders",
 *     description="Order Management Endpoints"
 * )
 */
class OrderController extends Controller
{
    public function __construct(private OrderService $orderService) {}

    /**
     * @OA\Get(
     *     path="/api/v1/orders",
     *     summary="Get all orders",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter orders by status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pending", "processing", "shipped", "delivered", "cancelled"})
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search orders by order number or customer email",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Orders retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Order")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['status', 'search']);

        if (auth()->user()->isCustomer()) {
            $filters['user_id'] = auth()->id();
        }

        $orders = $this->orderService->getAllOrders($filters);

        return response()->json($orders);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/orders",
     *     summary="Create a new order",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"shipping_address","billing_address","customer_email","items"},
     *             @OA\Property(property="shipping_address", type="string", example="123 Main St, City, State 12345"),
     *             @OA\Property(property="billing_address", type="string", example="123 Main St, City, State 12345"),
     *             @OA\Property(property="customer_email", type="string", format="email", example="customer@example.com"),
     *             @OA\Property(property="customer_phone", type="string", example="+1234567890"),
     *             @OA\Property(property="notes", type="string", example="Please deliver after 5 PM"),
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="product_id", type="integer", example=1),
     *                     @OA\Property(property="variant_id", type="integer", example=1),
     *                     @OA\Property(property="quantity", type="integer", example=2)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Order created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Order")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or insufficient stock"
     *     )
     * )
     */
    public function store(OrderRequest $request): JsonResponse
    {
        try {
            $order = $this->orderService->createOrder(auth()->user(), $request->validated());

            return response()->json($order, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/orders/{id}",
     *     summary="Get a specific order",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Order ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Order")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found"
     *     )
     * )
     */
    public function show(Order $order): JsonResponse
    {
        if (auth()->user()->isCustomer() && $order->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $order = $this->orderService->findOrder($order->id);

        return response()->json($order);
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/orders/{id}/status",
     *     summary="Update order status",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Order ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"processing", "shipped", "delivered", "cancelled"}, example="processing")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order status updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Order")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized - Vendor or Admin role required"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or invalid status transition"
     *     )
     * )
     */
    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        if (auth()->user()->isCustomer()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'status' => 'required|in:processing,shipped,delivered,cancelled',
        ]);

        try {
            $order = $this->orderService->updateOrderStatus($order, $request->status);

            return response()->json($order);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/orders/{id}/cancel",
     *     summary="Cancel an order",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Order ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order cancelled successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Order")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Order cannot be cancelled"
     *     )
     * )
     */
    public function cancel(Order $order): JsonResponse
    {
        if (auth()->user()->isCustomer() && $order->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $order = $this->orderService->cancelOrder($order);

            return response()->json($order);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/orders/{id}/invoice",
     *     summary="Download order invoice PDF",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Order ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Invoice PDF downloaded successfully",
     *         @OA\MediaType(
     *             mediaType="application/pdf",
     *             @OA\Schema(type="string", format="binary")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to generate invoice"
     *     )
     * )
     */
    public function downloadInvoice(Order $order, InvoiceService $invoiceService): JsonResponse
    {
        if (auth()->user()->isCustomer() && $order->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $path = $invoiceService->getInvoicePath($order);

            if (!$path) {
                $path = $invoiceService->generatePdf($order);
            }

            return response()->download(storage_path("app/{$path}"));
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to generate invoice'], 500);
        }
    }
}
