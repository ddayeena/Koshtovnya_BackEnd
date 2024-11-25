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
            'bead_producer_name' => optional($this->productDescription->beadProducer)->name,
            'is_in_wishlist' => $this->is_in_wishlist ?? false,
            'is_in_cart' => $this->is_in_cart ?? false,
        ];
    }
}
