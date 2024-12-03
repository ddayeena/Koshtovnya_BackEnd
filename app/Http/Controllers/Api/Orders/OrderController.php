<?php

namespace App\Http\Controllers\Api\Orders;

use App\Http\Controllers\Controller;
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
            'orders'=>OrderResource::collection($orders)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, string $id)
    {

       
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $user = $request->user();
        $order = Order::where('id', $id)
        ->where('user_id', auth()->id())
        ->firstOrFail();

        $products = $order->products;

        return response()->json([
            'products'=>OrderProductResource::collection($products)
        ]);

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
