<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductDescriptionResource extends JsonResource
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
            'name' => optional($this->product)->name,
            'price' => optional($this->product)->price,
            'image_url' => optional($this->product)->image_url,
            'country_of_manufacture' => $this->country_of_manufacture,
            'material' => 'Бісер',
            'type_of_fitting' => optional($this->product->fitting->first())->type_of_fitting,
            'type_of_bead' => $this->type_of_bead,
            'weight' => $this->weight,
            'sizes' => optional($this->product)->sizes->pluck('size_value'),
            'colors' => optional($this->product)->colors->pluck('color_name'),
            'bead_producer_name' => optional($this->beadProducer)->origin_country,
            'quantity' => optional($this->product)->quantity,
            'is_available' => optional($this->product)->quantity > 0,
            'is_in_wishlist' => $this->is_in_wishlist ?? false,
            'is_in_cart' => $this->is_in_cart ?? false,
            'notify_when_available' => $this->notify_when_available ?? false,
        ];
    }
}
