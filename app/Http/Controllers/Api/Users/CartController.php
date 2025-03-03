<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartProductResource;
use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Wishlist;
use App\Services\Cart\CartService;
use App\Services\Product\ProductService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    private $cart_service;

    public function __construct(CartService $cart_service)
    {
        $this->cart_service = $cart_service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //Get wishlist for authenticated user
        $cart = $request->user()->cart()->firstOrCreate([]);
        $products = $cart->products;

        return response()->json([
            'message' => 'Cart products retrieved successfully.',
            'products' => CartProductResource::collection($products),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'size' => 'nullable|string',
        ]);

        //Get product's ID
        $productId = $validated['product_id'];
        $quantity = $validated['quantity'];
        $size = $validated['size'] ?? null;

        $product = Product::findOrFail($productId);

        if (empty($size)) {
            $wishlist = $request->user()->wishlist;
            $wishlistProduct = $wishlist->products()->where('product_id', $productId)->first();
            //if user has that product in wishlist, then get this product's size
            if ($wishlistProduct) {
                $size = $wishlistProduct->pivot->size;
            } else {
                //if not, then get the first size of product by default
                $size = $product->productVariants->first()->size;
            }
        }
        $variant = $product->productVariants->where('size', $size)->first();
        //Change quantity of product 
        if ($variant) {
            $variant->quantity -= $quantity;
            $variant->save();
        }

        $cart = $request->user()->cart()->firstOrCreate([]);

        //Add product to the cart
        if (!$cart->products()->where('products.id', $productId)->exists()) {
            $cart->products()->attach($productId, [
                'size' => $size,
                'quantity' => $quantity,
            ]);
            return response()->json(['message' => 'Product added to cart']);
        }

        return response()->json(['message' => 'Product already in cart']);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //Get cart and operation
        $cart = $request->user()->cart()->firstOrCreate([]);
        $operation = $request->input('operation');
        $size = $request->input('size');

        if ($size) {
            $productVariant = ProductVariant::where('product_id', $id)
                ->where('size', $size)
                ->firstOrFail();
            if ($productVariant->quantity === 0) {
                return response()->json([
                    'message' => 'This product size is out of stock. Choose another one.',
                    'available_quantity' => 0,
                ], 400);
            } else if ($productVariant->quantity < $cart->products()->findOrFail($id)->pivot->quantity) {
                return response()->json([
                    'message' => 'Not enough stock available for this product size. Change the quantity of the product or choose another size. ',
                    'available_quantity' => $productVariant->quantity,
                ], 400);
            }


            if ($cart->products()->where('product_id', $id)->first()) {
                // Update product size in cart
                $cart->products()->updateExistingPivot($id, ['size' => $size]);
                return response()->json(['message' => 'Size updated successfully.'], 200);
            } else {
                return response()->json(['message' => 'Product not found in cart'], 404);
            }
        }

        //Update quantity
        else if ($operation)
            $response = $this->cart_service->updateProductQuantity($cart, $id, $operation);

        else {
            return response()->json(['message' => 'Operation or size field is required.'], 400);
        }
        return response()->json(['message' => $response['message']], $response['status']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        //Get cart for authenticated user
        $cart = $request->user()->cart()->firstOrCreate([]);

        //Check if the product exists in the cart
        $cart->products()->findOrFail($id);

        //Delete product
        $cart->products()->detach($id);
        return response()->json(['message' => 'Product removed from cart'], 200);
    }

    public function getCartCount(Request $request)
    {
        //Get cart and its count
        $cart = $request->user()->cart()->firstOrCreate([]);
        $itemCount = $cart->products->count();

        return response()->json(['cart_count' => $itemCount]);
    }
}
