<?php

namespace App\Services\Product;

use App\Models\BeadProducer;
use App\Models\Category;
use App\Models\Color;
use App\Models\Fitting;
use App\Models\Material;
use App\Models\Product;
use App\Models\ProductDescription;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

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

    public function createProduct($data)
    {
        DB::beginTransaction();

        try {
            //Upload image
            $imageData = $this->uploadImage($data['image']);
            //Create product
            $productDescription = $this->createProductDescription($data);
            $product = $this->createProductRecord($data, $productDescription->id, $imageData);

            //Crea details about product
            $this->createProductVariants($data['sizes'], $product->id);
            $this->attachColors($data['colors'], $product);
            $this->attachFittings($data['fittings'], $product);

            DB::commit();

            return [$product, $productDescription];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    //Upload image on cloudinary
    private function uploadImage($image)
    {
        $im = $image->storeOnCloudinary('products');
        return [
            'url' => $im->getSecurePath(),
            'public_id' => $im->getPublicId(),
        ];
    }

    private function createProductDescription($data)
    {
        //Get ids
        $beadProducerId = BeadProducer::where('origin_country', $data['bead_producer'])->value('id');
        $categoryId = Category::where('name', $data['category'])->value('id');

        return ProductDescription::create([
            'bead_producer_id' => $beadProducerId,
            'weight' => $data['weight'],
            'country_of_manufacture' => $data['country_of_manufacture'],
            'type_of_bead' => $data['type_of_bead'],
            'category_id' => $categoryId,
        ]);
    }

    private function createProductRecord($data, $descriptionId, $imageData)
    {
        return Product::create([
            'name' => $data['name'],
            'price' => $data['price'],
            'image_url' => $imageData['url'],
            'image_public_id' => $imageData['public_id'],
            'product_description_id' => $descriptionId,
        ]);
    }

    private function createProductVariants($sizes, $productId)
    {
        foreach ($sizes as $size) {
            ProductVariant::create([
                'product_id' => $productId,
                'size' => $size['size'],
                'quantity' => $size['quantity'],
            ]);
        }
    }

    private function attachColors($colors, $product)
    {
        $colorIds = Color::whereIn('color_name', $colors)->pluck('id')->toArray();
        $product->colors()->attach($colorIds);
    }

    private function attachFittings($fittings, $product)
    {
        $fittingData = [];
        foreach ($fittings as $fitting) {
            $fittingId = Fitting::where('name', $fitting['fitting'])->value('id');
            if ($fittingId) {
                $materialId = Material::where('name', $fitting['material'])->value('id');

                $fittingData[$fittingId] = [
                    'material_id' => $materialId,
                    'quantity' => $fitting['quantity']
                ];
            }
        }
        $product->fittings()->attach($fittingData);
    }
}
