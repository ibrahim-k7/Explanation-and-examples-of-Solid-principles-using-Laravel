<?php
namespace App\Services;

use App\Models\Order;

class OrderService
{
    public function createOrder($userId)
    {
        return Order::create([
            'user_id' => $userId,
            'status' => 'pending', // Assuming 'pending' status for new orders
        ]);
    }
}
