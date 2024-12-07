<?php

namespace App\Http\Resources\Order;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'order_date' => $this->created_at->translatedFormat('d F Y, H:i'),
            'status' => $this->status,
            'products' => OrderProductResource::collection($this->whenLoaded('products'))
        ];
    }
}
