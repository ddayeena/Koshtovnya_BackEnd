<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Http\Resources\WishlistProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //Get wishlist for authenticated user
        $wishlist = $request->user()->wishlist;

        if (!$wishlist) {
            return response()->json(['message' => 'Wishlist not found for this user.',], 404);
        }

        $products = $wishlist->products;
        return response()->json([
            'message' => 'Wishlist products retrieved successfully.',
            'products' => WishlistProductResource::collection($products),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //Get product's ID
        $productId = $request->input('product_id');
        $wishlist = $request->user()->wishlist()->firstOrCreate([]);

        //Add product to wishlist
        if (!$wishlist->products()->where('products.id', $productId)->exists()) {
            $wishlist->products()->attach($productId, [
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            return response()->json(['message' => 'Product added to wishlist']);
        }

        return response()->json(['message' => 'Product already in wishlist']);
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
        //Get wishlist for authenticated user
        $wishlist = $request->user()->wishlist()->firstOrCreate([]);
        if (!$wishlist) {
            return response()->json(['message' => 'Wishlist not found or access denied'], 404);
        }

        //Get product, then need to be destroyed
        $product = $wishlist->products()->where('products.id',$id)->first();
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
    
        //Delete product
        $wishlist->products()->detach($id);
        return response()->json(['message' => 'Product removed from wishlist'], 200);
    }
}
