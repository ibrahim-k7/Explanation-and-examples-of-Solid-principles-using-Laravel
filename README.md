
---

## Single Responsibility Principle

The Single Responsibility Principle (SRP) is one of the five core principles of software design (SOLID). This principle states that each class or software unit should have only one responsibility, meaning it should perform a single task or be responsible for one specific aspect.

### Example: Applying SRP to `CheckoutController`

Below is an example of a `CheckoutController` where the SRP violation occurs because payment processing logic is handled directly in the controller:

```php
public function checkout(Request $request)
{
    // Validate request data
    $validatedData = $request->validate([
        'user_id' => 'required|integer',
        'items' => 'required|array',
        'items.*.product_id' => 'required|integer',
        'items.*.quantity' => 'required|integer|min:1',
    ]);

    // Create a new order
    $order = Order::create([
        'user_id' => $validatedData['user_id'],
        'status' => 'pending', // Assuming 'pending' status for new orders
    ]);

    // Add order items
    foreach ($validatedData['items'] as $item) {
        $order->items()->create([
            'product_id' => $item['product_id'],
            'quantity' => $item['quantity'],
        ]);
    }

    // Process payment - violating SRP by handling payment logic directly in controller
    $paymentSuccessful = $this->processPayment($order);

    // Update order status after successful payment
    if ($paymentSuccessful) {
        $order->update(['status' => 'paid']);
        return response()->json(['message' => 'Order placed successfully'], 201);
    } else {
        return response()->json(['message' => 'Payment failed'], 400);
    }
}

// Payment processing logic directly in controller (violation of SRP)
private function processPayment($order)
{
    // Simulated payment processing logic
    return true;
}
```

### Refactoring to Follow SRP

1. **Move Validation to a Separate Request Class**:
   Instead of performing validation inside the controller, we can move it to a custom request class. This helps separate concerns, ensuring the controller only handles business logic and the request class manages validation.

   ```php
   public function rules(): array
   {
       return [
           'user_id' => 'required|integer',
           'items' => 'required|array',
           'items.*.product_id' => 'required|integer',
           'items.*.quantity' => 'required|integer|min:1',
       ];
   }
   ```

2. **Create Service Layers**:
   To adhere to SRP, we create separate services for handling different responsibilities:

   - **Payment Service**:
     ```php
     class PaymentService
     {
         public function processPayment(Order $order)
         {
             // Simulated payment processing logic
             return true;
         }
     }
     ```

   - **Order Service**:
     ```php
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
     ```

   - **Order Item Service**:
     ```php
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
     ```

   **Clean Dependencies in `CheckoutController`**:
   Inject the services into the `CheckoutController` to handle dependencies.

   ```php
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
   ```

Now, each class handles a specific responsibility, making the code more modular and easier to maintain.

--- 
