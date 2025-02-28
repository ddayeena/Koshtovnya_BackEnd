<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:50',
            'second_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'phone_number' => 'required|string|max:20',
            'city' => 'required|string|max:255',
            'delivery_name' => 'required|string|max:255',
            'delivery_address' => 'required|string|max:255',
            //'type_of_card' => 'nullable|string|max:255',
            //'payment_method' => 'required|in:Післяоплата,Оплата картою,Передоплата',
            'delivery_cost' => 'required|numeric|min:0',
            'cart_cost' => 'required|numeric|min:0',
        ];
    }
}
