<?php

namespace App\Http\Resources\Delivery;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'delivery_name' => optional($this->deliveryType)->name,
            'delivery_address' => $this->delivery_address,
            'delivery_cost' => $this->cost,
            'user' => optional($this->order->user)->last_name . ' ' . optional($this->order->user)->first_name  . ' ' . optional($this->order->user)->second_name,
            'phone_number' => optional($this->order->user)->phone_number
        ];
    }
}
