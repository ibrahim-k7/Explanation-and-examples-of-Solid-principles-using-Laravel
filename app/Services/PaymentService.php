<?php
namespace App\Services;

use App\Models\Order;

class PaymentService 
{
    public function processPayment(Order $order)
    {
        // Simulated payment processing logic
        // For demonstration purposes, let's assume payment is successful
        return true;
    }
}