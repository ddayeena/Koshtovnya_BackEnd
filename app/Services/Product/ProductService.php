<?php

namespace App\Services\Product;

use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\AdminProductDescriptionResource;
use App\Http\Resources\ProductDescriptionResource;
use App\Mail\ProductAvailableNotification;
use App\Models\BeadProducer;
use App\Models\Category;
use App\Models\Color;
use App\Models\Fitting;
use App\Models\Material;
use App\Models\Notification;
use App\Models\Product;
use App\Models\ProductDescription;
use App\Models\ProductVariant;
use App\Models\User;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

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
    public function uploadImage($image)
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


    public function updateProduct(Product $product, array $data)
    {
        DB::transaction(function () use ($product, $data) {
            $this->updateBasicFields($product, $data);
            $this->updateImage($product, $data);
            $this->updateProductDescription($product, $data);
            $this->updateFittings($product, $data);
            $this->updateSizes($product, $data);
            $this->updateColors($product, $data);
        });
    
        return  AdminProductDescriptionResource::make($product->productDescription);
    }
    
    private function updateBasicFields(Product $product, array $data)
    {
        $product->fill([
            'name' => $data['name'] ?? $product->name,
            'price' => $data['price'] ?? $product->price,
        ]);
        $product->save();
    }
    
    private function updateImage(Product $product, array $data)
    {
        if (isset($data['image'])) {
            Cloudinary::destroy($product->image_public_id);
            $image = $this->uploadImage($data['image']);
            $product->image_url = $image['url'];
            $product->image_public_id = $image['public_id'];
            $product->save();
        }
    }
    
    private function updateProductDescription(Product $product, array $data)
    {
        $productDescription = $product->productDescription;
        $data['category_id'] = $this->getCategoryId($data);
        $data['bead_producer_id'] = $this->getBeadProducerId($data);
        
        $productDescription->fill([
            'bead_producer_id' => $data['bead_producer_id'] ?? $productDescription->bead_producer_id,
            'weight' => $data['weight'] ?? $productDescription->weight,
            'country_of_manufacture' => $data['country_of_manufacture'] ?? $productDescription->country_of_manufacture,
            'type_of_bead' => $data['type_of_bead'] ?? $productDescription->type_of_bead,
            'category_id' => $data['category_id'] ?? $productDescription->category_id,
        ]);
        $productDescription->save();
    }
    
    private function getCategoryId(array $data)
    {
        if (isset($data['category'])) {
            $category = Category::where('name', $data['category'])->first();
            return $category ? $category->id : null;
        }
        return null;
    }
    
    private function getBeadProducerId(array $data)
    {
        if (isset($data['bead_producer'])) {
            $beadProducer = BeadProducer::where('origin_country', $data['bead_producer'])->first();
            return $beadProducer ? $beadProducer->id : null;
        }
        return null;
    }
    
    private function updateFittings(Product $product, array $data)
    {
        if (!isset($data['fittings'])) return;
        
        foreach ($data['fittings'] as $fitting) {
            $fittingModel = Fitting::where('name', $fitting['fitting'])->first();
            $materialModel = Material::where('name', $fitting['material'])->first();
    
            if ($fittingModel && $materialModel) {
                DB::table('fitting_product')->updateOrInsert([
                    'product_id' => $product->id,
                    'fitting_id' => $fittingModel->id,
                    'material_id' => $materialModel->id
                ], [
                    'quantity' => $fitting['quantity'] ?? 0
                ]);
            }
        }
    }
    
    private function updateSizes(Product $product, array $data)
    {
        if (!isset($data['sizes'])) return;
        
        foreach ($data['sizes'] as $size) {
            $existingVariant = $product->productVariants()->where('size', $size['size'])->first();
            if ($existingVariant) {
                if ($existingVariant->quantity == 0 && $size['quantity'] > 0) {
                    $this->notifyUsersAboutAvailability($product);
                }
                $existingVariant->update(['quantity' => $size['quantity']]);
            } else {
                $product->productVariants()->create($size);
            }
        }
    }
    
    private function notifyUsersAboutAvailability(Product $product)
    {
        $users = Notification::where('product_id', $product->id)
            ->whereNull('notified_at')
            ->get();
    
        foreach ($users as $notification) {
            $user = User::find($notification->user_id);
            if ($user) {
                Mail::to($user->email)->send(new ProductAvailableNotification($user, $product));
                $notification->update(['notified_at' => now()]);
            }
        }
    }
    
    private function updateColors(Product $product, array $data)
    {
        if (!isset($data['colors'])) return;
        
        $existingColors = $product->colors()->pluck('colors.id')->toArray();
        $newColors = Color::whereIn('color_name', $data['colors'])->pluck('id')->toArray();
        
        $colorsToDelete = array_intersect($existingColors, $newColors);
        $colorsToAdd = array_diff($newColors, $existingColors);
        
        $product->colors()->detach($colorsToDelete);
        $product->colors()->attach($colorsToAdd);
    }
}
