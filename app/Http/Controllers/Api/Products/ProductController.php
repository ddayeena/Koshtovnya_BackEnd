<?php

namespace App\Http\Controllers\Api\Products;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductDescriptionResource;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductDescription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */

     public function index(Request $request)
     {
         // Get token
         $token = $request->bearerToken();
         Log::info("Token: " . $token); 
         
         $user = null;  // By default, the user is not authenticated

         if ($token) {
             // If the token exists, validate it
             $accessToken = PersonalAccessToken::findToken($token);
             if ($accessToken) {
                 // If the token is valid, get the user
                 $user = $accessToken->tokenable;
             }
         }
         
         $products = Product::paginate(15);
         
         // Add information about whether the product is in the wishlist
         $products->getCollection()->transform(function ($product) use ($user) {
             if ($user) {
                 // Check if the product is in the wishlist
                 $product->is_in_wishlist = $user->wishlist->products->contains($product->id);
             } else {
                 // If the user is not authenticated or the token is invalid, set it to false
                 $product->is_in_wishlist = false;
             }
             return $product;
         });
         
         return ProductResource::collection($products);         
     }

    //display popular products
    public function popular()
    {
        $products = Product::with('productDescription')
            ->withCount('orders')  
            ->orderBy('orders_count', 'desc')  
            ->take(6)
            ->get();
    
        return ProductResource::collection($products);
    }

    public function newArrivals() 
    {
        $products = Product::with('productDescription')
            ->withCount('orders')
            ->orderBy('created_at', 'desc') 
            ->take(6) 
            ->get(); 
        return ProductResource::collection($products);
    }

    //display products by category 
    public function productsByCategory(int $id)
    {
        $category = Category::findOrFail($id);
        $productDescriptionIds = $category->productDescriptions->pluck('id');
        $products = Product::whereIn('product_description_id', $productDescriptionIds)->paginate(15);
        return ProductResource::collection($products);
    }

    //display products by name
    public function search(string $name)
    {
        $products = Product::where('name', 'LIKE', "%{$name}%")->get();
        return ProductResource::collection($products);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::findOrFail($id);
        return ProductDescriptionResource::make($product->productDescription);
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
