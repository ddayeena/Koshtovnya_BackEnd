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
            'bead_producer_name' => $this->productDescription ? $this->productDescription->beadProducer->name : null,
        ];
    }
}
