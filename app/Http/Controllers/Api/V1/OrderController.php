<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private OrderService $orderService) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['status', 'search']);

        if (auth()->user()->isCustomer()) {
            $filters['user_id'] = auth()->id();
        }

        $orders = $this->orderService->getAllOrders($filters);

        return response()->json($orders);
    }

    public function store(OrderRequest $request): JsonResponse
    {
        try {
            $order = $this->orderService->createOrder(auth()->user(), $request->validated());

            return response()->json($order, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function show(Order $order): JsonResponse
    {
        if (auth()->user()->isCustomer() && $order->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $order = $this->orderService->findOrder($order->id);

        return response()->json($order);
    }

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
