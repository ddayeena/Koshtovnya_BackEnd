<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'bead_producer_name' => optional($this->productDescription->beadProducer)->translation()?->name
                      ?? optional($this->productDescription->beadProducer)->name,

            'is_in_wishlist' => $this->is_in_wishlist ?? false,
            'is_in_cart' => $this->is_in_cart ?? false,
            'is_deleted' => $this->deleted_at !== null,
            'rating' => (float) ($this->reviews_avg_rating ?? 0),
            'review_count' => $this->reviews_count ?? 0,
            'variants' => $this->productVariants(),

        ];
    }

    /**
     * Get product variants
     */
    private function productVariants()
    {
        return $this->productVariants
            ->sortBy('size') //Sort by size
            ->map(function ($variant) {
                return [
                    'size' => $variant->size,
                    'quantity' => $variant->quantity,
                    'is_available' => $variant->quantity > 0,
                ];
            })->values();
    }
}
