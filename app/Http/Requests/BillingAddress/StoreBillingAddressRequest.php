<?php

namespace App\Http\Requests\BillingAddress;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBillingAddressRequest extends FormRequest
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => 'nullable|string|min:2',
            'last_name' => 'nullable|string|min:2',
            'user_id' => 'nullable|exists:users,id',
            'country_id' => 'required|exists:countries,id',
            'email' => 'nullable|string|email|unique:users,email',
            'phone_number' => 'nullable|string',
            'state_id' => 'required|exists:states,id',
            'full_address' => 'required|string|min:5',
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'The user field is required.',
            'country_id.required' => 'The country field is required.',
            'state_id.required' => 'The state field is required.',
            'full_address.required' => 'The full address field is required.',
        ];
    }
}
