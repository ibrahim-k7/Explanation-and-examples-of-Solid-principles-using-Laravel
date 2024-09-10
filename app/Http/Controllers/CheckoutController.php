<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Services\PaymentService;
use App\Services\OrderItemService;
use App\Services\OrderService;

use Illuminate\Http\Request;

class CheckoutController extends Controller {
    protected $orderService;
    protected $orderItemService;
    protected $paymentService;

    public function __construct(OrderService $orderService, OrderItemService $orderItemService, PaymentService $paymentService) {
        $this->orderService = $orderService;
        $this->orderItemService = $orderItemService;
        $this->paymentService = $paymentService;
    }

    public function checkout(CheckoutRequest $request) {
        // Validated data is available via the $request object

        // Create a new order using OrderService
        $order = $this->orderService->createOrder($request->user->id);

        // Add order items using OrderItemService
        $this->orderItemService->addItemsToOrder($order, $request->items);

        // Process payment using PaymentService
        $paymentSuccessful = $this->paymentService->processPayment($order);

        // Update order status after successful payment
        if ($paymentSuccessful) {
            $order->update(['status' => 'paid']);
            // Return response
            return response()->json(['message' => 'Order placed successfully!'], 201);
        } else {
            // Payment failed, handle accordingly
            return response()->json(['message' => 'Payment failed'], 400);
        }
    }
}