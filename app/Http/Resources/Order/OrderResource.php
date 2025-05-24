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

    protected static float $rate = 1;
    protected static string $currency = 'uah';

    public static function setCurrency(string $currency, float $rate): void
    {
        static::$currency = $currency;
        static::$rate = $rate;
    }

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_date' => $this->created_at->translatedFormat('d F Y, H:i'),
            'status' => $this->status,
            'amount' => static::$currency === 'usd'
                ? number_format($this->total_amount / static::$rate, 2, '.', '')
                : number_format($this->total_amount, 2, '.', ''),
            'currency' => static::$currency,
            'products' => OrderProductResource::collection($this->whenLoaded('products'))
        ];
    }
}
