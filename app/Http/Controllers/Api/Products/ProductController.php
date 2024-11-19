<?php

namespace App\Http\Controllers\Api\Products;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductDescriptionResource;
use App\Models\Product;
use App\Models\ProductDescription;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index()
    {
        $products = Product::paginate(15); 
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

    public function newArrivals() {
        $products = Product::with('productDescription')
            ->withCount('orderProducts')
            ->orderBy('created_at', 'desc') 
            ->take(6) 
            ->get(); 
        return ProductResource::collection($products);
    }

    //display products by category 
    public function productsByCategory(int $id)
    {
        $productDescriptionIds = ProductDescription::where('category_id', $id)->pluck('id');
        $products = Product::whereIn('product_description_id', $productDescriptionIds)->get();
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
