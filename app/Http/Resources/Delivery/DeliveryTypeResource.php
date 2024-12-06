<?php

namespace App\Http\Resources\Delivery;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryTypeResource extends JsonResource
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
            'delivery_type' => $this->type
        ];
    }
}
