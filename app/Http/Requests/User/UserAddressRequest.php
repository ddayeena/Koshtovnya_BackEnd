<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UserAddressRequest extends FormRequest
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
            'phone_number' => 'required|string|max:20|regex:/^\+?[0-9\s\-]+$/', 
            'delivery_name' => 'required|string|max:255', 
            'city' => 'required|string|max:255', 
            'post_office' => 'required_without:delivery_address|string|max:255',
            'delivery_address' => 'required_without:post_office|string|max:255',
        ];
    }
}
