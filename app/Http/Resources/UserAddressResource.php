<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserAddressResource extends JsonResource
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
            'user' => optional($this->user)->last_name . ' ' . optional($this->user)->first_name  . ' ' . optional($this->user)->second_name,
            'phone_number' => optional($this->user)->phone_number,
            'delivery_type' => optional($this->deliveryType)->type,
            'city' => $this->city,
            'delivery_name' => optional($this->deliveryType)->name,
            'delivery_address' => $this->delivery_address,
        ];
    } 
}
