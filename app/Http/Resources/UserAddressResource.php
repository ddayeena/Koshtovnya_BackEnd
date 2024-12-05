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
            'user_first_name' => optional($this->user)->first_name,
            'user_last_name' => optional($this->user)->last_name,
            'phone_number' => optional($this->user)->phone_number,
            'city' => $this->city,
            'post_service' => $this->post_service,
            'post_office' => $this->post_office,
            'delivery_type' => $this->delivery_type,
            'delivery_address' => $this->delivery_address,
        ];
    }
}