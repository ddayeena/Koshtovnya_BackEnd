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
            'name' => $this->product ? $this->product->name : null,
            'price' => $this->product ? $this->product->price : null,
            'image_url' => $this->product ? $this->product->image_url : null,
            'country_of_manufacture' => $this->country_of_manufacture,
            'material' => 'Бісер',
            'type_of_fitting' => $this->fitting->isNotEmpty() ? $this->fitting->first()->type_of_fitting : null,  // Отримуємо першу фурнітуру
            'type_of_bead' => $this->type_of_bead,
            'weight' => $this->weight,
            'sizes' => $this->sizes->pluck('size_value'),
            'colors' => $this->colors->pluck('color_name'),
            'bead_producer_name' => $this->beadProducer ? $this->beadProducer->origin_country : null,
        ];
    }
}
