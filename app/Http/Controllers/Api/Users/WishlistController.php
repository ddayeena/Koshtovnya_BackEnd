<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Http\Resources\WishlistProductResource;
use App\Models\Product;
use App\Services\ExchangeRateService;
use App\Services\Product\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class WishlistController extends Controller
{
    private $product_service;
    private $exchange_rate_service;

    public function __construct(ProductService $product_service, ExchangeRateService $exchange_rate_service)
    {
        $this->product_service = $product_service;
        $this->exchange_rate_service = $exchange_rate_service;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //Get product's ID
        $productId = $request->input('product_id');
        $size = $request->input('size');
        if (empty($size)) {
            $product = Product::findOrFail($productId);
            $size = $product->productVariants->first()->size;
        }

        $wishlist = $request->user()->wishlist()->firstOrCreate([]);
        //Add product to wishlist
        if (!$wishlist->products()->where('products.id', $productId)->exists()) {
            $wishlist->products()->attach($productId, [
                'size' => $size,
            ]);
            return response()->json(['message' => 'Product added to wishlist']);
        }
        return response()->json(['message' => 'Product already in wishlist']);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        $locale = request('lang', app()->getLocale());
        App::setLocale($locale);

        //Get wishlist for authenticated user
        $wishlist = $request->user()->wishlist()->firstOrCreate([]);
        $products = $this->product_service->attachCartInfo($wishlist->products, $request->user());

        ['currency' => $currency, 'rate' => $rate] = $this->exchange_rate_service->resolveCurrencyData($request);
        WishlistProductResource::setCurrency($currency, $rate);
        
        return response()->json([
            'message' => 'Wishlist products retrieved successfully.',
            'products' => WishlistProductResource::collection($products),
        ]);
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
