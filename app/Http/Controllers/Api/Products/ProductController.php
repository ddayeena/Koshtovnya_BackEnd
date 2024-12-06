<?php

namespace App\Http\Controllers\Api\Products;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\FilterRequest;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductDescriptionResource;
use App\Models\Product;
use App\Services\Product\ProductFilterService;
use App\Services\Product\ProductService;
use App\Services\User\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    private $product_service;
    private $user_service;
    private $product_filter_service;

    public function __construct(ProductService $product_service, UserService $user_service, ProductFilterService $product_filter_service)
    {
        $this->product_service = $product_service;
        $this->user_service = $user_service;
        $this->product_filter_service = $product_filter_service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(FilterRequest $request)
    {
        $user = $this->user_service->getUserFromRequest($request);

        $products = $this->product_filter_service->getFilteredProducts($request->validated(), $user);

        return ProductResource::collection($products);
    }

    public function filter()
    {
        return  $this->product_filter_service->getFilter();
    }

    //display popular products
    public function popular(Request $request)
    {
        $user = $this->user_service->getUserFromRequest($request);
        $products = Product::with('productDescription')
            ->withCount('orders')
            ->orderBy('orders_count', 'desc')
            ->take(6)
            ->get();

        $products = $this->product_service->attachWishlistInfo($products, $user);
        $products = $this->product_service->attachCartInfo($products, $user);

        return ProductResource::collection($products);
    }

    public function newArrivals(Request $request)
    {
        $user = $this->user_service->getUserFromRequest($request);
        $products = Product::with('productDescription')
            ->withCount('orders')
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get();

        $products = $this->product_service->attachWishlistInfo($products, $user);
        $products = $this->product_service->attachCartInfo($products, $user);

        return ProductResource::collection($products);
    }

    //display products by category 
    public function productsByCategory(FilterRequest $request, int $id)
    {
        $user = $this->user_service->getUserFromRequest($request);

        $products = $this->product_service->getProductsByCategory($id);

        $products = $this->product_filter_service->getFilteredProducts($request->validated(), $user);

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
    public function show(Request $request, string $id)
    {
        $user = $this->user_service->getUserFromRequest($request);
        
        $product = Product::findOrFail($id);
        $product=$this->product_service->attachUserProductStatus($product,$user);

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
