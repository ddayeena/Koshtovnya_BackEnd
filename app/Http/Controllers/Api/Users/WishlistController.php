<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Http\Resources\WishlistProductResource;
use App\Models\Product;
use App\Services\Product\ProductService;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    private $product_service;

    public function __construct(ProductService $product_service)
    {
        $this->product_service = $product_service;
    }

    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        //Get wishlist for authenticated user
        $wishlist = $request->user()->wishlist()->firstOrCreate([]);
        $products = $this->product_service->attachCartInfo($wishlist->products, $request->user());

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
        $size = $request->input('size');
        if(empty($size)){
            $product = Product::findOrFail($productId);
            $size = $product->productVariants->first()->size;
        }
        
        $wishlist = $request->user()->wishlist()->firstOrCreate([]);
        //Add product to wishlist
        if (!$wishlist->products()->where('products.id', $productId)->exists()) {
            $wishlist->products()->attach($productId, [
                'size' => $size,
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
        $wishlist->products()->findOrFail($id);
        
        //Delete product
        $wishlist->products()->detach($id);
        return response()->json(['message' => 'Product removed from wishlist'], 200);
    }
}
