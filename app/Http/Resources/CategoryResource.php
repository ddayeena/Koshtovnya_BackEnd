<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $locale = $request->get('lang', app()->getLocale());
        $translation = $this->translation($locale);

        return [
            'id' => $this->id,
            'name' => $translation?->name ?? null,
            'image_url' => $this->image_url,
        ];
    }
}
