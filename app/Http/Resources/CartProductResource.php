<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartProductResource extends JsonResource
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
            'name' => app()->getLocale() === 'en' ? $this->name_en : $this->name_uk,
            'price' => static::$currency === 'usd'
                ? number_format($this->price / static::$rate, 2, '.', '')
                : number_format($this->price, 2, '.', ''),
            'currency' => static::$currency,
            'quantity' => $this->pivot->quantity,
            // 'is_available' => $this->quantity > $this->pivot->quantity,
            'image_url' => $this->image_url,
            'selected_size' => $this->pivot->size,
            'variants' =>  $this->productVariants(),
        ];
    }

        /**
     * Get product variants
     */
    private function productVariants()
    {
        return $this->productVariants
            ->sortBy('size') // Сортуємо за ключем size_value
            ->map(function ($variant) {
                return [
                    'size' => $variant->size,
                    'quantity' => $variant->quantity,
                    'is_available' => $variant->quantity > 0, // Перевіряємо, чи кількість більша за 0
                ];
            })->values(); // Перевпорядковуємо індекси після сортування
    }
}
