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
            'name' => $this->name,
            'price' => static::$currency === 'usd'
                ? number_format($this->price / static::$rate, 2, '.', '')
                : number_format($this->price, 2, '.', ''),

            'currency' => static::$currency,
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
