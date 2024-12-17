<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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
            'image' => 'required|image|mimes:jpg,jpeg,png|max:2048', 
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'bead_producer' => 'required|string|max:50',
            'country_of_manufacture' => 'required|string|max:100',
            'type_of_bead' => 'required|in:Матовий,Прозорий',
            'fittings' => 'required|array',
            'fittings.*.fitting' => 'required|string|max:100',
            'fittings.*.quantity' => 'required|numeric|min:0',
            'fittings.*.material' => 'required|string|max:100',
            'weight' => 'required|numeric|min:0',
            'sizes' => 'required|array',
            'sizes.*.size' => 'required|numeric|min:0',
            'sizes.*.quantity' => 'required|numeric|min:0',
            'colors' => 'required|array',
        ];
    }
}
