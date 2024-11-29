<?php

namespace App\Services;

use App\Http\Filter\ProductFilter;
use App\Models\BeadProducer;
use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\ProductDescription;
use App\Models\Size;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;

class ProductService
{
    public function getUserFromRequest(Request $request)
    {
        // Get token
        $token = $request->bearerToken();
        Log::info("Token: " . $token);

        if ($token) {
            // If the token exists, validate it
            $accessToken = PersonalAccessToken::findToken($token);
            if ($accessToken) {
                // If the token is valid, get the user
                return $accessToken->tokenable;
            }
        }

        // If there is no token or if the token is not valid, return null
        return null;
    }

    public function attachWishlistInfo($products, $user)
    {
        //if the user is authenticated, check all product if they are in the wishlist
        if ($user) {
            $wishlistProducts = $user->wishlist->products->pluck('id')->toArray();
            foreach ($products as $product) {
                $product->is_in_wishlist = in_array($product->id, $wishlistProducts);
            }
        } else {
            // If the user is not authenticated, mark all products as not in the wishlist
            foreach ($products as $product) {
                $product->is_in_wishlist = false;
            }
        }
        return $products;
    }

    public function attachCartInfo($products, $user)
    {
        if ($user) {
            //if the user is authenticated, check all product if they are in the cart
            $cartProducts = $user->cart->products->pluck('id')->toArray();
            foreach ($products as $product) {
                $product->is_in_cart = in_array($product->id, $cartProducts);
            }
        } else {
            // If the user is not authenticated, mark all products as not in the cart
            foreach ($products as $product) {
                $product->is_in_cart = false;
            }
        }
        return $products;
    }

    public function getProductsByCategory(int $categoryId)
    {
        //Return products by category with description
        $category = Category::findOrFail($categoryId);
        $productDescriptionIds = $category->productDescriptions->pluck('id');
        return Product::whereIn('product_description_id', $productDescriptionIds)->paginate(15);
    }

    public function updateProductQuantity($cart, $productId, string $operation): array
    {
        $product = $cart->products()->where('products.id', $productId)->first();

        if (!$product) {
            return ['status' => 404, 'message' => 'Product not found'];
        }

        switch ($operation) {
            case 'increase':
                $product->pivot->quantity++;
                break;

            case 'decrease':
                if ($product->pivot->quantity > 1) {
                    $product->pivot->quantity--;
                } else {
                    return ['status' => 400, 'message' => 'Cannot decrease quantity below 1'];
                }
                break;

            default:
                return ['status' => 400, 'message' => 'Invalid operation'];
        }

        $product->pivot->save();
        return ['status' => 200, 'message' => 'Quantity updated successfully'];
    }

    public function getFilteredProducts(array $filters, $user)
    {
        // Create filter
        $filter = app()->make(ProductFilter::class, ['params' => $filters]);

        //Get filtered products and paginate
        $productQuery = Product::filter($filter);
        $products = $productQuery->paginate(15);

        // Add wishlist and cart info
        $products = $this->attachWishlistInfo($products, $user);
        $products = $this->attachCartInfo($products, $user);

        return $products;
    }

    public function getFilter()
    {
        $is_available =[
            ['name'=> 'Немає в наявності', 'count'=> Product::where('quantity','=',0)->count()],
            ['name'=> 'В наявності', 'count'=> Product::where('quantity','>',0)->count()]
        ];

        $sizes = Size::pluck('size_value');
        $colors = Color::pluck('color_name');

        $type_of_bead = [
            ['name'=>'Матовий', 'count'=>ProductDescription::where('type_of_bead','Матовий')->count()],
            ['name'=>'Не матовий', 'count'=>ProductDescription::where('type_of_bead','Не матовий')->count()]
        ];


        $bead_producer = BeadProducer::withCount('productDescriptions')
        ->get()
        ->map(function ($producer) {
            return [
                'origin_country' => $producer->origin_country,
                'count' => $producer->product_descriptions_count,
            ];
        });

        $weight = [
            'min' => ProductDescription::min('weight'),
            'max' => ProductDescription::max('weight'),
        ];
    
        $price = [
            'min' => Product::min('price'),
            'max' => Product::max('price'),
        ];


        return response()->json([
            'Доступність' => $is_available,
            'Розмір' => $sizes,
            'Колір' => $colors,
            'Тип бісеру' => $type_of_bead,
            'Виробник бісеру' => $bead_producer,
            'Вага' => $weight,
            'Ціна' => $price,
        ]);
    }
}
