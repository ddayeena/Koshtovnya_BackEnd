<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            "last_name" => $this->last_name,
            "first_name" => $this->first_name,
            "second_name" => $this->second_name,
            "email" => $this->email,
            "phone_number" => $this->phone_number,
            "role" => $this->role,
        ];
    }
}
