<?php

namespace App\Http\Controllers\Api\Orders;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\OrderRequest;
use App\Http\Resources\Delivery\DeliveryResource;
use App\Http\Resources\Order\PaymentResource;
use App\Http\Resources\Order\OrderResource;
use App\Models\Order;
use App\Services\Order\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }
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

        try {
            // Process order
            $result = $this->orderService->processOrder($data, $request->user());

            //Return result
            return response()->json([
                'message' => 'Order created successfully!',
                'data' => [
                    'order' => OrderResource::make($result['order']),
                    'delivery' => DeliveryResource::make($result['delivery']),
                    'payment' => PaymentResource::make($result['payment']),
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create order.',
                'errors' => json_decode($e->getMessage(), true) ?? $e->getMessage(),
            ], 400);
        }
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
