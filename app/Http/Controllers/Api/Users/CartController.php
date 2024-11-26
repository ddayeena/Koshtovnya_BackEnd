<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartProductResource;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //Get wishlist for authenticated user
        $cart = $request->user()->cart;
        if (!$cart) {
            return response()->json(['message' => 'Cart not found'], 404);
        }
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        //Get cart for authenticated user
        $cart = $request->user()->cart()->firstOrCreate([]);
        if (!$cart) {
            return response()->json(['message' => 'Cart not found'], 404);
        }

        //Get product, then need to be destroyed
        $product = $cart->products()->where('products.id',$id)->first();
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
    
        //Delete product
        $cart->products()->detach($id);
        return response()->json(['message' => 'Product removed from cart'], 200);
    }
}
