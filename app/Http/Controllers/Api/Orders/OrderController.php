<?php

namespace App\Http\Controllers\Api\Orders;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\OrderRequest;
use App\Http\Resources\Delivery\DeliveryResource;
use App\Http\Resources\Order\OrderResource;
use App\Models\Delivery;
use App\Models\DeliveryType;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\UserAddress;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //Get orders for authenticated user
        $orders = $request->user()->orders()->with('products')->get();

        return response()->json([
            'message' => 'Orders retrieved successfully.',
            'orders' => OrderResource::collection($orders)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(OrderRequest $request)
    {
        $data = $request->validated();
        $cart = $request->user()->cart;

        if (!$cart || $cart->products->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], 400);
        }

        $errors = [];

        foreach ($cart->products as $product) {
            if ($product->quantity === 0) {
                $errors[] = [
                    'message' => "Product is out of stock, remove it from your cart to place an order.",
                    'product_name' => $product->name,
                    'available_quantity' => 0,
                ];
            } elseif ($product->quantity < $product->pivot->quantity) {
                $errors[] = [
                    'message' => "Only {$product->quantity} products left in stock, remove it from your cart or change the quantity to place an order.",
                    'product_name' => $product->name,
                    'available_quantity' => $product->quantity,
                ];
            }
        }
        
        if (!empty($errors)) {
            return response()->json([
                'message' => 'Some products are not available in the required quantity.',
                'errors' => $errors,
            ], 400);
        }

        $totalAmount = $data['delivery_cost'] + $data['cart_cost'];
        $order = Order::create([
            'user_id' => $request->user()->id,
            'first_name' => $data['first_name'],
            'second_name' => $data['second_name'],
            'last_name' => $data['last_name'],
            'phone_number' => $data['phone_number'],
            'total_amount' => $totalAmount,
        ]);

        foreach ($cart->products as $product) {
            $order->products()->attach($product->id, [
                'quantity' => $product->pivot->quantity,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $cart->products()->detach();

        $delivery_type_id = DeliveryType::where('name', $data['delivery_name'])->value('id');
        if (!$delivery_type_id) {
            return response()->json(['message' => 'Delivery type not found'], 404);
        }

        $delivery = Delivery::create([
            'order_id' => $order->id,
            'city' => $data['city'],
            'delivery_type_id' => $delivery_type_id,
            'delivery_address' => $data['delivery_address'],
            'cost' => $data['delivery_cost']
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'type_of_card' => $data['type_of_card'],
            'payment_method' => $data['payment_method'],
            'amount' => $totalAmount,
        ]);

        $userAddress = $request->user()->userAddress;

        if (!$userAddress) {
            $userAddress = UserAddress::create([
                'user_id' => $request->user()->id,
                'delivery_type_id' => $delivery_type_id,
                'city' => $data['city'],
                'delivery_address' => $data['delivery_address'],
            ]);
        }

        foreach ($order->products as $product) {
            $product->quantity -= $product->pivot->quantity; 
            if ($product->quantity < 0) {
                return response()->json(['message' => 'Insufficient stock for product: ' . $product->name], 400);
            }
            $product->save();
        }

        return response()->json([
            'message' => 'Order created successfully!',
            'order' => OrderResource::make($order),
            'delivery' => DeliveryResource::make($delivery),
            'payment' => $payment
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $order = Order::with('products')
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();


        return response()->json([
            'data' => [
                'order' => OrderResource::make($order),
                'total_cost' => $order->total_amount,
                'delivery' => DeliveryResource::make($order->delivery),
                'payment_method' => $order->payment->payment_method,
            ]
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
