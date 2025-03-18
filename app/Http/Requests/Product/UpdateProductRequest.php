<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
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
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'name' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:100',
            'price' => 'nullable|numeric|min:0',
            'bead_producer' => 'nullable|string|max:50',
            'country_of_manufacture' => 'nullable|string|max:100',
            'type_of_bead' => 'nullable|in:Матовий,Прозорий',
            'weight' => 'nullable|numeric|min:0',
            'colors' => 'nullable|array',
            'fittings' => 'nullable|array',
            'fittings.*.fitting' => 'nullable|string|max:100|required_with:fittings.*.quantity,fittings.*.material',
            'fittings.*.quantity' => 'nullable|numeric|min:0|required_with:fittings.*.fitting,fittings.*.material',
            'fittings.*.material' => 'nullable|string|max:100|required_with:fittings.*.fitting,fittings.*.quantity',
            'sizes' => 'nullable|array',
            'sizes.*.size' => ['nullable', 'numeric', 'min:0', 'required_with:sizes.*.quantity'],
            'sizes.*.quantity' => ['nullable', 'numeric', 'min:0', 'required_with:sizes.*.size'],

        ];
    }
}
