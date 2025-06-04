<?php

namespace App\Http\Controllers\Api\Products;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\FilterRequest;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\AdminProductDescriptionResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductDescriptionResource;
use App\Models\BeadProducer;
use App\Models\Category;
use App\Models\Color;
use App\Models\Fitting;
use App\Models\Material;
use App\Models\Product;
use App\Models\Review;
use App\Services\ExchangeRateService;
use App\Services\Product\ProductFilterService;
use App\Services\Product\ProductService;
use App\Services\User\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    private $product_service;
    private $user_service;
    private $product_filter_service;
    private $exchange_rate_service;

    public function __construct(
        ProductService $product_service,
        UserService $user_service,
        ProductFilterService $product_filter_service,
        ExchangeRateService $exchange_rate_service
    ) {
        $this->product_service = $product_service;
        $this->user_service = $user_service;
        $this->product_filter_service = $product_filter_service;
        $this->exchange_rate_service = $exchange_rate_service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(FilterRequest $request)
    {
        $user = $this->user_service->getUserFromRequest($request);
        ['currency' => $currency, 'rate' => $rate] = $this->exchange_rate_service->resolveCurrencyData($request);
    
        $filters = $request->validated();
    
        if ($currency === 'usd') {
            if (isset($filters['price_from'])) {
                $filters['price_from'] = round($filters['price_from'] * $rate, 2);
            }
    
            if (isset($filters['price_to'])) {
                $filters['price_to'] = round($filters['price_to'] * $rate, 2);
            }
        }
    
        $products = $this->product_filter_service->getFilteredProducts(
            $filters,
            $user,
            $request->isAdminPanel
        );
    
        $products->load(['productDescription'])
            ->loadCount('reviews')
            ->loadAvg('reviews', 'rating');
    
        ProductResource::setCurrency($currency, $rate);
    
        return ProductResource::collection($products);
    }
    
    //get filter fields
    public function filter(Request $request)
    {
        $categoryId = $request->query('category_id');
        $currency = $request->query('currency', 'uah');
        $locale = request('lang', app()->getLocale());
        App::setLocale($locale);

        return $this->product_filter_service->getFilter($categoryId, $currency, $locale);
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

        $products->loadCount('reviews')
            ->loadAvg('reviews', 'rating');

        $products = $this->product_service->attachWishlistInfo($products, $user);
        $products = $this->product_service->attachCartInfo($products, $user);

        ['currency' => $currency, 'rate' => $rate] = $this->exchange_rate_service->resolveCurrencyData($request);
        ProductResource::setCurrency($currency, $rate);

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

        $products->loadCount('reviews')
            ->loadAvg('reviews', 'rating');
        $products = $this->product_service->attachWishlistInfo($products, $user);
        $products = $this->product_service->attachCartInfo($products, $user);

        ['currency' => $currency, 'rate' => $rate] = $this->exchange_rate_service->resolveCurrencyData($request);
        ProductResource::setCurrency($currency, $rate);

        return ProductResource::collection($products);
    }

    //display products by category 
    public function productsByCategory(FilterRequest $request, int $id)
    {
        $user = $this->user_service->getUserFromRequest($request);
        ['currency' => $currency, 'rate' => $rate] = $this->exchange_rate_service->resolveCurrencyData($request);
    
        $filters = $request->validated();
        if ($currency === 'usd') {
            if (isset($filters['price_from'])) {
                $filters['price_from'] = $filters['price_from'] * $rate;
            }
            if (isset($filters['price_to'])) {
                $filters['price_to'] = $filters['price_to'] * $rate;
            }
        }
    
        $productQuery = Product::whereHas('productDescription', function ($query) use ($id) {
            $query->where('category_id', $id);
        })->with('productDescription');
    
        $products = $this->product_filter_service->getFilteredProducts(
            $filters,
            $user,
            false,
            $productQuery
        );
    
        $products = $this->product_service->attachWishlistInfo($products, $user);
        $products = $this->product_service->attachCartInfo($products, $user);
    
        $products->loadCount('reviews')
            ->loadAvg('reviews', 'rating');
    
        ProductResource::setCurrency($currency, $rate);
    
        return ProductResource::collection($products);
    }
    

    //display products by name
    public function search(Request $request, string $name)
    {
        $products = Product::where('name', 'LIKE', "%{$name}%")->get();
        ['currency' => $currency, 'rate' => $rate] = $this->exchange_rate_service->resolveCurrencyData($request);
        ProductResource::setCurrency($currency, $rate);

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
    public function formData(Request $request)
    {
        $locale = request('lang', app()->getLocale());
        App::setLocale($locale);

        $categories = Category::with(['translations' => function ($query) use ($locale) {
            $query->where('locale', $locale);
        }])
            ->withCount('productDescriptions')
            ->get()
            ->map(function ($category) use ($locale) {
                $translation = $category->translations->first();
                return $translation ? $translation->name : $category->name;
            });
        $bead_producers = BeadProducer::pluck('origin_country');
        $colors = Color::getNamesByLocale($locale);
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
        $locale = request('lang', app()->getLocale());
        App::setLocale($locale);

        $product = Product::withTrashed()
        ->with(['colors.translations']) // завантажує переклади кольорів
        ->find($id);
    

        if ($product->trashed()) {
            if ($request->isAdminPanel) {
                return $this->showTrashed($id, $request);
            } else {
                return response()->json(['message' => 'Product not found'], 404);
            }
        }

        $product = $this->product_service->attachUserProductStatus($product, $user);

        // Середній рейтинг і кількість відгуків
        $product->productDescription->rating = (float)Review::where('product_id', $id)->avg('rating');
        $product->productDescription->review_count = Review::where('product_id', $id)->count();

        // Додаткове поле overall_rating (розгорнута статистика)
        $reviews = Review::where('product_id', $id)->whereNull('deleted_at');

        $breakdown = $reviews
            ->select('rating', DB::raw('count(*) as count'))
            ->groupBy('rating')
            ->pluck('count', 'rating')
            ->toArray();

        $fullBreakdown = [];
        foreach (range(1, 5) as $i) {
            $fullBreakdown[(string)$i] = $breakdown[$i] ?? 0;
        }

        $product->productDescription->ratings_breakdown = $fullBreakdown;

        ['currency' => $currency, 'rate' => $rate] = $this->exchange_rate_service->resolveCurrencyData($request);

        if ($request->isAdminPanel) {
            AdminProductDescriptionResource::setCurrency($currency, $rate);
            return AdminProductDescriptionResource::make($product->productDescription);
        }
        ProductDescriptionResource::setCurrency($currency, $rate);
        return ProductDescriptionResource::make($product->productDescription);
    }


    public function showTrashed(string $id, Request $request)
    {
        $product = Product::withTrashed()
            ->with([
                'productDescription' => function ($query) {
                    $query->withTrashed();
                },
                'productVariants' => function ($query) {
                    $query->withTrashed();
                },
                'reviews' => function ($query) {
                    $query->withTrashed();
                },
            ])
            ->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $colors = DB::table('color_product')
            ->join('colors', 'color_product.color_id', '=', 'colors.id')
            ->where('color_product.product_id', $id)
            ->whereNotNull('color_product.deleted_at')
            ->select('colors.color_name')
            ->get();

        $fittings = DB::table('fitting_product')
            ->join('fittings', 'fitting_product.fitting_id', '=', 'fittings.id')
            ->join('materials', 'fitting_product.material_id', '=', 'materials.id')
            ->where('fitting_product.product_id', $id)
            ->whereNotNull('fitting_product.deleted_at')
            ->select('fittings.name as fitting', 'materials.name as material', 'fitting_product.quantity')
            ->get();

        $averageRating = (float) $product->reviews->avg('rating');
        $reviewCount = $product->reviews->count();

        ['currency' => $currency, 'rate' => $rate] = $this->exchange_rate_service->resolveCurrencyData($request);

        return response()->json([
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
                'category' => $product->productDescription->category->translated_name,
                'price' => round($product->price / $rate, 2),
                'currency' => $currency,
                'image_url' => $product->image_url,
                'country_of_manufacture' =>  $product->productDescription->country_of_manufacture,
                'material' => 'Бісер',
                'type_of_fitting' => $fittings->map(function ($fitting) {
                    return [
                        'fitting_name' => $fitting->fitting,
                        'quantity' => $fitting->quantity,
                        'material_name' => $fitting->material,
                    ];
                }),
                'type_of_bead' =>  $product->productDescription->type_of_bead,
                'weight' =>  $product->productDescription->weight,
                'variants' => $product->productVariants->map(function ($variant) {
                    return [
                        'size' => $variant->size,
                        'quantity' => $variant->quantity,
                        'is_available' => $variant->quantity > 0,
                    ];
                }),
                'colors' => $colors->pluck("color_name"),
                'bead_producer_name' => $product->productDescription->beadProducer->origin_country,
                'rating' =>  $averageRating,
                'review_count' =>  $reviewCount,
            ]
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, string $id)
    {
        $product = Product::findOrFail($id);
        $productResource = $this->product_service->updateProduct($product, $request->validated());

        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $productResource,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::beginTransaction();

        try {
            $product = Product::findOrFail($id);

            // Softdelete
            $product->productDescription()->delete();
            DB::table('color_product')
                ->where('product_id', $id)
                ->update(['deleted_at' => now()]);
            DB::table('fitting_product')
                ->where('product_id', $id)
                ->update(['deleted_at' => now()]);
            $product->productVariants()->delete();

            $reviews = $product->reviews;
            foreach ($reviews as $review) {
                $review->replies()->delete();
                $review->delete();
            }
            $product->delete();

            // Full delete 
            DB::table('cart_product')->where('product_id', $id)->delete();
            DB::table('product_wishlist')->where('product_id', $id)->delete();
            DB::table('notifications')->where('product_id', $id)->delete();

            DB::commit();

            return response()->json(['message' => 'Product deleted successfully.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error', 'error' => $e->getMessage()], 500);
        }
    }

    public function restore(string $id)
    {
        DB::beginTransaction();

        try {
            $product = Product::withTrashed()->findOrFail($id);

            // Restore all data
            $product->productDescription()->restore();
            DB::table('color_product')
                ->where('product_id', $id)
                ->update(['deleted_at' => null]);
            DB::table('fitting_product')
                ->where('product_id', $id)
                ->update(['deleted_at' => null]);
            $product->productVariants()->restore();

            $reviews = $product->reviews()->onlyTrashed()->get();
            foreach ($reviews as $review) {
                $review->replies()->restore();
                $review->restore();
            }
            $product->restore();

            DB::commit();

            return response()->json(['message' => 'Product restored successfully.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error', 'error' => $e->getMessage()], 500);
        }
    }
}
