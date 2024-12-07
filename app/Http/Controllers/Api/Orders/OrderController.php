<?php

namespace App\Http\Controllers\Api\Orders;

use App\Http\Controllers\Api\Orders\Delivery\NovaPoshtaController;
use App\Http\Controllers\Controller;
use App\Http\Resources\Delivery\DeliveryResource;
use App\Http\Resources\Order\OrderProductResource;
use App\Http\Resources\Order\OrderResource;
use App\Models\Order;
use App\Models\Product;
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
    public function store(Request $request, string $id) {}

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $order = Order::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return response()->json([
            'data' => [
                'order' => OrderResource::make($order),
                'total_cost' => $order->total_amount,
                'delivery' => DeliveryResource::make($order->delivery),
                'payment_method' => $order->payment->payment_method,
            ]
        ],200);
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
