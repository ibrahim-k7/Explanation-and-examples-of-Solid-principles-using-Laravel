<?php

namespace App\Services;

use App\Models\Order;

class OrderItemService
{
    public function addItemsToOrder(Order $order, array $items)
    {
        foreach ($items as $item) {
            $order->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
            ]);
            // You might also want to store price or calculate it based on product
        }
    }
}
