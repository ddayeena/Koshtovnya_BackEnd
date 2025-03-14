<?php

namespace App\Http\Controllers\Api\Products;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\FilterRequest;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\AdminProductDescriptionResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductDescriptionResource;
use App\Mail\ProductAvailableNotification;
use App\Models\BeadProducer;
use App\Models\Category;
use App\Models\Color;
use App\Models\Fitting;
use App\Models\Material;
use App\Models\Notification;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Services\Product\ProductFilterService;
use App\Services\Product\ProductService;
use App\Services\User\UserService;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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

    //get filter fields
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
        $products = Product::whereHas('productDescription', function ($query) use ($id) {
            $query->where('category_id', $id);
        })->with('productDescription')->paginate(15);
        $products = $this->product_filter_service->getFilteredProducts($request->validated(), $user);

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

        if ($user->role === 'user') {
            return ProductDescriptionResource::make($product->productDescription);
        }
        return AdminProductDescriptionResource::make($product->productDescription);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, string $id)
    {
        $data = $request->validated();
        $product = Product::findOrFail($id);

        DB::transaction(function () use ($product, $data) {
            // Оновлення основних полів у `products`
            $product->fill([
                'name' => $data['name'] ?? $product->name,
                'price' => $data['price'] ?? $product->price,
            ]);

            // Оновлення зображення
            if (isset($data['image'])) {
                Cloudinary::destroy($product->image_public_id);
                $image = $this->product_service->uploadImage($data['image']);
                $product->image_url = $image['url'];
                $product->image_public_id = $image['public_id'];
            }

            $product->save();

            // Оновлення `product_descriptions`
            $productDescription = $product->productDescription;

            // Знаходимо category_id за переданою назвою
            if (isset($data['category'])) {
                $category = Category::where('name', $data['category'])->first();
                if ($category) {
                    $data['category_id'] = $category->id;
                }
            }

            // Знаходимо bead_producer_id за переданою назвою
            if (isset($data['bead_producer'])) {
                $beadProducer = BeadProducer::where('origin_country', $data['bead_producer'])->first();
                if ($beadProducer) {
                    $data['bead_producer_id'] = $beadProducer->id;
                }
            }

            // Оновлюємо `product_descriptions`
            $productDescription->fill([
                'bead_producer_id' => $data['bead_producer_id'] ?? $productDescription->bead_producer_id,
                'weight' => $data['weight'] ?? $productDescription->weight,
                'country_of_manufacture' => $data['country_of_manufacture'] ?? $productDescription->country_of_manufacture,
                'type_of_bead' => $data['type_of_bead'] ?? $productDescription->type_of_bead,
                'category_id' => $data['category_id'] ?? $productDescription->category_id,
            ]);

            $productDescription->save();

            // Оновлення fittings
            if (isset($data['fittings'])) {
                foreach ($data['fittings'] as $fitting) {
                    $fittingModel = Fitting::where('name', $fitting['fitting'])->first();
                    $materialModel = Material::where('name', $fitting['material'])->first();

                    if ($fittingModel && $materialModel) {
                        // Перевіряємо, чи є вже такий fitting з таким material у продукту
                        $existingFitting = DB::table('fitting_product')
                            ->where('product_id', $product->id)
                            ->where('fitting_id', $fittingModel->id)
                            ->where('material_id', $materialModel->id)
                            ->first();

                        if ($existingFitting) {
                            // Якщо є, то видаляємо його
                            DB::table('fitting_product')
                                ->where('product_id', $product->id)
                                ->where('fitting_id', $fittingModel->id)
                                ->where('material_id', $materialModel->id)
                                ->delete();
                        } else {
                            // Якщо немає, додаємо новий
                            $product->fittings()->attach($fittingModel->id, [
                                'material_id' => $materialModel->id,
                                'quantity' => $fitting['quantity'] ?? 0
                            ]);
                        }
                    }
                }
            }


            // Оновлення sizes
            if (isset($data['sizes'])) {
                foreach ($data['sizes'] as $size) {
                    $existingVariant = $product->productVariants()->where('size', $size['size'])->first();

                    if ($existingVariant) {
                        if ($existingVariant->quantity == 0 && $size['quantity'] > 0) {
                            $users = Notification::where('product_id', $product->id)
                            ->whereNull('notified_at') // Перевіряємо, що користувач ще не був повідомлений
                            ->get();

                            foreach ($users as $notification) {
                                $user = User::find($notification->user_id);
                    
                                if ($user) {
                                    // Надсилаємо email (можеш замінити на реальну логіку)
                                    Mail::to($user->email)->send(new ProductAvailableNotification($user, $product));

                                    // Оновлюємо час сповіщення
                                    $notification->update(['notified_at' => now()]);
                                }
                            }

                        }
                        // Оновлюємо кількість, якщо розмір уже існує
                        $existingVariant->update(['quantity' => $size['quantity']]);
                    } else {
                        // Додаємо новий розмір, якщо його ще немає
                        $product->productVariants()->create([
                            'size' => $size['size'],
                            'quantity' => $size['quantity'],
                        ]);
                    }
                }
            }


            // Оновлення кольорів
            if (isset($data['colors'])) {
                $existingColors = $product->colors()->pluck('colors.id')->toArray(); // Поточні кольори товару
                $newColors = Color::whereIn('color_name', $data['colors'])->pluck('id')->toArray(); // ID переданих кольорів

                $colorsToDelete = array_intersect($existingColors, $newColors); // Кольори, що треба видалити
                $colorsToAdd = array_diff($newColors, $existingColors); // Кольори, що треба додати

                $product->colors()->detach($colorsToDelete); // Видаляємо кольори
                $product->colors()->attach($colorsToAdd); // Додаємо нові
            }
        });

        return response()->json([
            'message' => 'Product updated successfully',
            'product' => ProductDescriptionResource::make($product->productDescription)
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
