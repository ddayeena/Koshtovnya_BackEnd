<?php

namespace App\Services\Product;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;

class ProductService
{
    //Attach product information with information about whether it is added to the user's wishlist
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

    //Attach product information with information about whether it is added to the user's cart
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

    //Get products from one category
    public function getProductsByCategory(int $categoryId)
    {
        //Return products by category with description
        $category = Category::findOrFail($categoryId);
        $productDescriptionIds = $category->productDescriptions->pluck('id');
        return Product::whereIn('product_description_id', $productDescriptionIds)->paginate(15);
    }

    //Update product quantity in the cart
    public function updateProductQuantity($cart, $productId, string $operation): array
    {
        $product = $cart->products()->findOrFail($productId);
        $availableQuantity = ProductVariant::findOrFail($productId)->quantity;

        switch ($operation) {
            case 'increase':
                if($product->pivot->quantity < $availableQuantity){
                    $product->pivot->quantity++;
                }
                else{
                    return ['status' => 400, 'message' => 'Not enough stock available.'];
                }
                break;

            case 'decrease':
                if ($product->pivot->quantity > 1) {
                    $product->pivot->quantity--;
                } else {
                    return ['status' => 400, 'message' => 'Cannot decrease quantity below 1'];
                }
                break;

            default:  return ['status' => 400, 'message' => 'Invalid operation'];
        }

        $product->pivot->save();
        return ['status' => 200, 'message' => 'Quantity updated successfully'];
    }

    //Attach 
    public function attachUserProductStatus($product, $user)
    {
        $product->productDescription->is_in_wishlist = $user 
        ? $user->wishlist->products()->where('product_id', $product->id)->exists()
        : false;

        $product->productDescription->is_in_cart = $user 
        ? $user->cart->products()->where('product_id', $product->id)->exists()
        : false;
        
        $product->productDescription->notify_when_available = $user 
        ? $user->notifications()->where('product_id', $product->id)->exists()
        : false;

        return $product;
    }
}
