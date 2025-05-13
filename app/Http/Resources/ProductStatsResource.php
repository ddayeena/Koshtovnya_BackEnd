<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductStatsResource extends JsonResource
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

            'is_deleted' => $this->deleted_at !== null,
            'rating' => (float) ($this->reviews_avg_rating ?? 0),
            'review_count' => $this->reviews_count ?? 0,
        ];
    }
}
