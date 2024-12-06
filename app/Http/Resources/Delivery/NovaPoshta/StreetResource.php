<?php

namespace App\Http\Resources\Delivery\NovaPoshta;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StreetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'street' => $this['StreetsType'] . ' ' . $this['Description']
        ];
    }
}
