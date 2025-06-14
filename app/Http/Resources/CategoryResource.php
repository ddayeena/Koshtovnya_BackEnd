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

        // Отримуємо переклади на обидві мови
        $translationUk = $this->translations->where('locale', 'uk')->first();
        $translationEn = $this->translations->where('locale', 'en')->first();

        return [
            'id' => $this->id,
            'name' => $translation?->name ?? null,
            'name_uk' => $translationUk?->name ?? null,
            'name_en' => $translationEn?->name ?? null,
            'image_url' => $this->image_url,
        ];
    }
}
