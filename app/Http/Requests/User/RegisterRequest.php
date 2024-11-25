<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8', 
        ];
    }

    public function messages()
    {
        return [
            'first_name.required' => 'Ім’я є обов’язковим.',
            'first_name.string' => 'Ім’я має бути текстом.',
            'first_name.max' => 'Ім’я не може містити більше ніж 255 символів.',
        
            'last_name.required' => 'Прізвище є обов’язковим.',
            'last_name.string' => 'Прізвище має бути текстом.',
            'last_name.max' => 'Прізвище не може містити більше ніж 255 символів.',
        
            'email.required' => 'Електронна пошта є обов’язковою',
            'email.max' => 'Електронна пошта не може містити більше ніж 255 символів.',
            'email.unique' => 'Ця електронна пошта вже використовується.',
            'email.email' => 'Невірний формат електронної пошти.',
        
            'password.required' => 'Пароль є обов’язковим.',
            'password.string' => 'Пароль має бути текстом.',
            'password.min' => 'Пароль повинен містити щонайменше 8 символів.',
        ];
    }
}
