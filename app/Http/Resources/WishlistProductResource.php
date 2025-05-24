<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\ProductVariant;

class WishlistProductResource extends JsonResource
{
    protected static float $rate = 1;
    protected static string $currency = 'uah';

    public static function setCurrency(string $currency, float $rate): void
    {
        static::$currency = $currency;
        static::$rate = $rate;
    }

    public function toArray(Request $request): array
    {
        // Отримаємо розмір і продукт з pivot (тобто з product_wishlist)
        $productId = $this->id;
        $size = $this->pivot->size ?? null;

        // Знайдемо відповідний варіант товару
        $variant = ProductVariant::where('product_id', $productId)
            ->where('size', $size)
            ->where('quantity', '>', 0)
            ->first();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => static::$currency === 'usd'
                ? number_format($this->price / static::$rate, 2, '.', '')
                : number_format($this->price, 2, '.', ''),
            'currency' => static::$currency,
            'image_url' => $this->image_url,
            'is_in_cart' => $this->is_in_cart ?? false,
            'is_available' => $variant !== null, // true якщо знайшли з кількістю > 0
        ];
    }
}
