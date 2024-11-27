<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Api\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Resources\CartProductResource;
use Illuminate\Http\Request;

class CartController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //Get wishlist for authenticated user
        $cart = $request->user()->cart()->firstOrCreate([]);
        $products = $cart->products;

        return response()->json([
            'message' => 'Cart products retrieved successfully.',
            'products' => CartProductResource::collection($products),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //Get product's ID
        $productId = $request->input('product_id');
        $cart = $request->user()->cart()->firstOrCreate([]);
        
        //Add product to wishlist
        if (!$cart->products()->where('products.id', $productId)->exists()) {
            $cart->products()->attach($productId, [
                'quantity' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            return response()->json(['message' => 'Product added to cart']);
        }
        return response()->json(['message' => 'Product already in cart']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //Get cart and operation
        $cart = $request->user()->cart()->firstOrCreate([]);
        $operation = $request->input('operation');

        //Update quantity
        $response = $this->service->updateProductQuantity($cart, $id, $operation);

        return response()->json(['message' => $response['message']], $response['status']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        //Get cart for authenticated user
        $cart = $request->user()->cart()->firstOrCreate([]);

        //Check if the product exists in the cart
        if (!$cart->products()->where('products.id', $id)->exists()) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        //Delete product
        $cart->products()->detach($id);
        return response()->json(['message' => 'Product removed from cart'], 200);
    }

    public function getCartCount(Request $request)
    {
        //Get cart and its count
        $cart = $request->user()->cart()->firstOrCreate([]);
        $itemCount = $cart->products->count();
        
        return response()->json(['cart_count' => $itemCount]);
    }
}
