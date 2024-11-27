<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\WishlistProductResource;
use Illuminate\Http\Request;

class WishlistController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //Get wishlist for authenticated user
        $wishlist = $request->user()->wishlist()->firstOrCreate([]);
        $products = $this->service->attachCartInfo($wishlist->products, $request->user());
        
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

        //Check if the product exists in the wishlist
        if (!$wishlist->products()->where('products.id',$id)->exists()) {
            return response()->json(['message' => 'Product not found'], 404);
        }
    
        //Delete product
        $wishlist->products()->detach($id);
        return response()->json(['message' => 'Product removed from wishlist'], 200);
    }
}
