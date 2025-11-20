<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Repositories\OrderItemRepository;
use App\Repositories\OrderRepository;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        private OrderRepository $orderRepository,
        private OrderItemRepository $orderItemRepository,
        private InventoryService $inventoryService
    ) {}

    public function getAllOrders(array $filters = [])
    {
        return $this->orderRepository->getAll($filters);
    }

    public function createOrder(User $user, array $data): Order
    {
        return DB::transaction(function () use ($user, $data) {
            $orderNumber = 'ORD-' . now()->format('YmdHis') . rand(1000, 9999);

            $orderData = [
                'order_number' => $orderNumber,
                'user_id' => $user->id,
                'total_amount' => 0,
                'shipping_address' => $data['shipping_address'],
                'billing_address' => $data['billing_address'],
                'customer_email' => $data['customer_email'],
                'customer_phone' => $data['customer_phone'] ?? null,
                'notes' => $data['notes'] ?? null,
            ];

            $order = $this->orderRepository->create($orderData);
            $totalAmount = 0;

            foreach ($data['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $variant = isset($item['variant_id']) ? ProductVariant::find($item['variant_id']) : null;

                if (!$this->inventoryService->checkStock($product, $variant, $item['quantity'])) {
                    throw new \Exception("Insufficient stock for product: {$product->name}");
                }

                $unitPrice = $variant ? $variant->price : $product->price;
                $itemTotal = $unitPrice * $item['quantity'];

                $this->orderItemRepository->create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_variant_id' => $variant?->id,
                    'product_name' => $product->name,
                    'sku' => $variant ? $variant->sku : $product->sku,
                    'unit_price' => $unitPrice,
                    'quantity' => $item['quantity'],
                    'total_price' => $itemTotal,
                ]);

                $this->inventoryService->deductStock($product, $variant, $item['quantity']);
                $totalAmount += $itemTotal;
            }

            $order->update(['total_amount' => $totalAmount]);

            return $order->load('items');
        });
    }

    public function cancelOrder(Order $order): Order
    {
        return DB::transaction(function () use ($order) {
            if (!$order->canBeCancelled()) {
                throw new \Exception('Order cannot be cancelled');
            }

            foreach ($order->items as $item) {
                $product = $item->product;
                $variant = $item->variant;

                $this->inventoryService->restoreStock($product, $variant, $item->quantity);
            }

            $order->cancel();

            return $order->fresh('items');
        });
    }

    public function updateOrderStatus(Order $order, string $status): Order
    {
        $oldStatus = $order->status;

        switch ($status) {
            case 'shipped':
                $order->markAsShipped();
                break;
            case 'delivered':
                $order->markAsDelivered();
                break;
            default:
                $this->orderRepository->update($order, ['status' => $status]);
                break;
        }

        if ($oldStatus !== $status) {
            $order->user->notify(new OrderStatusUpdated($order, $oldStatus, $status));
        }

        return $order->fresh('items');
    }

    public function findOrder(int $id): ?Order
    {
        return $this->orderRepository->findById($id);
    }
}
