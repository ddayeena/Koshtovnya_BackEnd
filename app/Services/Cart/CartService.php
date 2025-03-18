<?php

namespace App\Services\Cart;


use App\Models\ProductVariant;

class CartService
{
    //Update product quantity in the cart
    public function updateProductQuantity($cart, $productId, string $operation): array
    {
        $product = $cart->products()->findOrFail($productId);
    
        //Get product's size from cart
        $size = $product->pivot->size;
    
        //Get product variant in order to get size
        $productVariant = ProductVariant::where('product_id', $productId)
                                        ->where('size', $size)
                                        ->first();
    
        if (!$productVariant) {
            return ['status' => 400, 'message' => 'Product variant not found'];
        }
    
        $availableQuantity = $productVariant->quantity;
    
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

}
