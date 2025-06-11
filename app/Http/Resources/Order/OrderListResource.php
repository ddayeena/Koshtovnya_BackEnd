<?php

namespace App\Http\Resources\Order;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderListResource extends JsonResource
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
            'order_date' => $this->created_at->translatedFormat('d.m.Y'),
            'status' => __('orders.status.' . $this->status),
            'phone_number' => $this->phone_number,
            'products'=> app()->getLocale() === 'en' ? $this->products->pluck('name_en') : $this->products->pluck('name_uk')
        ];
    }
}
