<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TopLatestReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $locale = $request->get('lang', app()->getLocale());
        Carbon::setLocale($locale);

        return [
            'id' => $this->id,
            'user_first_name' => optional($this->user)->first_name,
            'user_last_name' => optional($this->user)->last_name,
            'comment' => $this->comment,
            'rating' => $this->rating,
            'date' => $this->created_at->translatedFormat('d F Y, H:i'),
        ];
    }
}
