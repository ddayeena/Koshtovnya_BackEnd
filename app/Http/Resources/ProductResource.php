<?php

namespace App\Http\Resources;

use App\Models\ProductDescription;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
            'image_url' => $this->image_url,
            'bead_producer_name' => $this->productDescription && $this->productDescription->beadProducer
                ? $this->productDescription->beadProducer->name
                : null,

            'is_in_wishlist' => $this->is_in_wishlist ?? false,
            'is_in_cart' => $this->is_in_cart ?? false,
            'is_deleted' => $this->deleted_at !== null,
            'rating' => (float) ($this->reviews_avg_rating ?? 0),
            'review_count' => $this->reviews_count ?? 0,
            'variants' => $this->productVariants(),
        ];
    }
    
    /**
     * Get product variants
     */
    private function productVariants()
    {
        return $this->productVariants
            ->sortBy('size') //Sort by size
            ->map(function ($variant) {
                return [
                    'size' => $variant->size,
                    'quantity' => $variant->quantity,
                    'is_available' => $variant->quantity > 0,
                ];
            })->values();
    }
}
