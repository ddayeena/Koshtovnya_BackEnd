<?php

namespace App\Http\Resources\Delivery;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type_of_card' => $this->type_of_card,
            'payment_method' => $this->payment_method,
            'transaction_number' => $this->transaction_number,
            'amount' =>  $this->amount,
        ];
    }
}
