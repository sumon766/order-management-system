<?php

namespace App\Repositories;

use App\Models\Order;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderRepository
{
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = Order::with(['user', 'items.product', 'items.variant']);

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['search'])) {
            $query->where('order_number', 'like', '%' . $filters['search'] . '%')
                ->orWhere('customer_email', 'like', '%' . $filters['search'] . '%');
        }

        return $query->orderBy('created_at', 'desc')->paginate(15);
    }

    public function findById(int $id): ?Order
    {
        return Order::with(['user', 'items.product', 'items.variant'])->find($id);
    }

    public function findByOrderNumber(string $orderNumber): ?Order
    {
        return Order::with(['user', 'items.product', 'items.variant'])
            ->where('order_number', $orderNumber)
            ->first();
    }

    public function create(array $data): Order
    {
        return Order::create($data);
    }

    public function update(Order $order, array $data): bool
    {
        return $order->update($data);
    }

    public function getOrdersByStatus(string $status): LengthAwarePaginator
    {
        return Order::with(['user', 'items'])
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->paginate(15);
    }
}
