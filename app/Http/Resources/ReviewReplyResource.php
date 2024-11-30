<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewReplyResource extends JsonResource
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
            'admin_first_name' => optional($this->admin)->first_name,
            'admin_last_name' => optional($this->admin)->last_name,
            'comment' => $this->comment,
            'date' => $this->created_at->translatedFormat('d F Y, H:i'),
        ];
    }
}
