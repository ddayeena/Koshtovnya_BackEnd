<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class FilterRequest extends FormRequest
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
            'is_available' => 'nullable|array',
            'is_available.*' => 'in:0,1', // 0 - is NOT available, 1 - is available

            'size_from' => 'nullable|numeric|min:0',
            'size_to' =>  'nullable|numeric|min:0|gte:size_from',
            'color' => 'nullable|string',
            'type_of_bead' => 'nullable|array',

            'bead_producer' => 'nullable|array',
            'bead_producer.*' => 'exists:bead_producer_translations,origin_country',

            'weight_from' => 'nullable|numeric|min:0',
            'weight_to' => 'nullable|numeric|min:0|gte:weight_from',

            'price_from' => 'nullable|numeric|min:0',
            'price_to' => 'nullable|numeric|min:0|gte:price_from',

            'category' => 'nullable|array',
            'category.*' => 'exists:category_translations,name',

            'rating' => 'nullable|array',
            'rating.*' => 'in:1,2,3,4,5',

            'is_deleted' => 'nullable|array',
            'is_deleted.*' => 'nullable|in:0,1',

            'currency' => 'in:usd,uah'
        ];
    }
}
