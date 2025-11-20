<?php

namespace App\Repositories;

use App\Models\OrderItem;

class OrderItemRepository
{
    public function create(array $data): OrderItem
    {
        return OrderItem::create($data);
    }

    public function getItemsByOrder(int $orderId)
    {
        return OrderItem::with(['product', 'variant'])
            ->where('order_id', $orderId)
            ->get();
    }
}
