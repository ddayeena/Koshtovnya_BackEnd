<?php

namespace App\Http\Controllers\Api\Products;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\FilterRequest;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductDescriptionResource;
use App\Models\BeadProducer;
use App\Models\Category;
use App\Models\Color;
use App\Models\Fitting;
use App\Models\Material;
use App\Models\Product;
use App\Models\Review;
use App\Services\Product\ProductFilterService;
use App\Services\Product\ProductService;
use App\Services\User\UserService;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;

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
        $products = $this->product_filter_service->getFilteredProducts($request->validated(), $user);
        $products = Product::whereHas('productDescription', function ($query) use ($id) {
            $query->where('category_id', $id);
        })->with('productDescription')->paginate(15);

        $products = $this->product_service->attachWishlistInfo($products, $user);
        $products = $this->product_service->attachCartInfo($products, $user);
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
    public function store(StoreProductRequest $request)
    {
        try {
            [$product, $productDescription] = $this->product_service->createProduct($request->validated());

            return response()->json([
                'message' => 'Product created successfully',
                'product' => [$product, $productDescription],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating product',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    //Returns data required when creating a product
    public function formData()
    {
        $categories = Category::pluck('name');
        $bead_producers = BeadProducer::pluck('origin_country');
        $colors = Color::pluck('color_name');
        $fittings = Fitting::pluck('name');
        $materials = Material::pluck('name');
        $type_of_bead = ['Матовий', 'Прозорий'];
        $countries_of_manufacture = ['Україна'];


        return response()->json([
            'data' => [
                'categories' => $categories,
                'bead_producers' => $bead_producers,
                'colors' => $colors,
                'fittings' => $fittings,
                'materials' => $materials,
                'type_of_bead' => $type_of_bead,
                'countries_of_manufacture' => $countries_of_manufacture
            ]
        ]);
    }


    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $user = $this->user_service->getUserFromRequest($request);

        $product = Product::findOrFail($id);
        $averageRating = (float)Review::where('product_id', $id)->avg('rating');
        $reviewCount = Review::where('product_id', $id)->count();        

        $product = $this->product_service->attachUserProductStatus($product, $user);
        $product->productDescription->rating = $averageRating;
        $product->productDescription->review_count = $reviewCount;

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
