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
            'delivery_name' => __('delivery_types.' . optional($this->deliveryType)->name),
            'delivery_address' => $this->delivery_address,
            'delivery_cost' => number_format($this->cost / self::$rate, 2, '.', ''),
            'currency' => self::$currency,
            'user' => optional($this->order)->last_name . ' ' . optional($this->order)->first_name  . ' ' . optional($this->order)->second_name,
            'phone_number' => optional($this->order)->phone_number
        ];
    }
}
