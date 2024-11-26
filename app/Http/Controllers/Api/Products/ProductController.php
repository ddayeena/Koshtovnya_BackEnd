<?php

namespace App\Http\Controllers\Api\Products;

use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductDescriptionResource;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends BaseController
{
    /**
     * Display a listing of the resource.
     */

     public function index(Request $request)
     {
        $user = $this->service->getUserFromRequest($request);
         
        $products = Product::paginate(15);

        $products = $this->service->attachWishlistInfo($products,$user);
        $products = $this->service->attachCartInfo($products,$user);

        return ProductResource::collection($products);         
     }

    //display popular products
    public function popular(Request $request)
    {
        $user = $this->service->getUserFromRequest($request);
        $products = Product::with('productDescription')
            ->withCount('orders')  
            ->orderBy('orders_count', 'desc')  
            ->take(6)
            ->get();

        $products = $this->service->attachWishlistInfo($products,$user);
        $products = $this->service->attachCartInfo($products,$user);

        return ProductResource::collection($products);
    }

    public function newArrivals(Request $request) 
    {
        $user = $this->service->getUserFromRequest($request);
        $products = Product::with('productDescription')
            ->withCount('orders')
            ->orderBy('created_at', 'desc') 
            ->take(6) 
            ->get(); 

        $products = $this->service->attachWishlistInfo($products,$user);
        $products = $this->service->attachCartInfo($products,$user);

        return ProductResource::collection($products);
    }

    //display products by category 
    public function productsByCategory(Request $request, int $id)
    {
        $user = $this->service->getUserFromRequest($request);

        $products = $this->service->getProductsByCategory($id);
        
        $products = $this->service->attachWishlistInfo($products,$user);
        $products = $this->service->attachCartInfo($products,$user);

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
