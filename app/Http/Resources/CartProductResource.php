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
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
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
