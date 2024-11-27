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
}
