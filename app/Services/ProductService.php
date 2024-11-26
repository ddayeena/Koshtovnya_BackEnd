<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
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
        if ($user) {
            $wishlistProducts = $user->wishlist->products->pluck('id')->toArray();
            foreach ($products as $product) {
                $product->is_in_wishlist = in_array($product->id, $wishlistProducts);
            }
        } else {
            foreach ($products as $product) {
                $product->is_in_wishlist = false;
            }
        }
        return $products;
    }

    public function attachCartInfo($products, $user)
    {
        if ($user) {
            $cartProducts = $user->cart->products->pluck('id')->toArray();
            foreach ($products as $product) {
                $product->is_in_cart = in_array($product->id, $cartProducts);
            }
        } else {
            foreach ($products as $product) {
                $product->is_in_cart = false;
            }
        }
        return $products;
    }

    public function getProductsByCategory(int $categoryId)
    {
        $category = Category::findOrFail($categoryId);
        $productDescriptionIds = $category->productDescriptions->pluck('id');
        return Product::whereIn('product_description_id', $productDescriptionIds)->paginate(15);
    }
}
