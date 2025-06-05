<?php

namespace App\Http\Resources;

use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminProductDescriptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */

    protected static $currency = 'uah';
    protected static $rate = 1;

    public static function setCurrency(string $currency, float $rate): void
    {
        self::$currency = $currency;
        self::$rate = $rate;
    }

    public function toArray(Request $request): array
    {
        return [
            'id' => optional($this->product)->id,
            'name' => optional($this->product)->name,
            'category' => $this->category->translated_name,
            'price' => number_format(optional($this->product)->price / self::$rate, 2, '.', ''),
            'currency' => self::$currency,
            'image_url' => optional($this->product)->image_url,
            'country_of_manufacture' => $this->country_of_manufacture,
            'material' => 'Бісер',
            'type_of_fitting' => $this->getFittings(),
            'type_of_bead' => $this->type_of_bead,
            'weight' => $this->weight,
            'variants' => $this->productVariants(),
            'colors' => optional($this->product)->colors->map(function ($color) {
                return optional($color->translation())->color_name;
            })->filter()->values(),
            'bead_producer_name' => optional($this->beadProducer)->translation()?->origin_country
                      ?? optional($this->beadProducer)->origin_country,
            'rating' => $this->rating ?? 0,
            'review_count' => $this->review_count ?? 0,
            'ratings_breakdown' => (object)($this->ratings_breakdown ?? []),
        ];
    }

    /**
     * Get product variants
     */
    private function productVariants()
    {
        return $this->product->productVariants
            ->sortBy('size') //Sort by size
            ->map(function ($variant) {
                return [
                    'size' => $variant->size,
                    'quantity' => $variant->quantity,
                    'is_available' => $variant->quantity > 0,
                ];
            })->values();
    }

    private function getFittings()
    {
        return $this->product->fittings
            ->map(function ($fitting) {
                $material = Material::find($fitting->pivot->material_id);

                return [
                    'fitting' => $fitting->name,
                    'quantity' => $fitting->pivot->quantity,
                    'material' => $material ? $material->name : null,
                ];
            })
            ->unique()
            ->values();
    }
}
