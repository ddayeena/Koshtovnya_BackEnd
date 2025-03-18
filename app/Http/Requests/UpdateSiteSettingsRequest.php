<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSiteSettingsRequest extends FormRequest
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
            'site_logo' => 'nullable|image|max:2048', 
            'footer_email_info' => 'nullable|email',
            'footer_address_info' => 'nullable|string',
            'footer_phone_number' => 'nullable|string',
        ];
    }
}
