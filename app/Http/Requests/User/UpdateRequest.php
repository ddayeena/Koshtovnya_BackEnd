<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
            'last_name' => 'nullable|string|max:50',
            'first_name' => 'nullable|string|max:50',
            'second_name' => 'nullable|string|max:50',
            'email' => 'nullable|string|email|max:255|unique:users,email,' . $this->user()->id,
            'phone_number' => 'nullable|string|max:20|regex:/^\+?[0-9\s\-]+$/',
        ];
    }
}
