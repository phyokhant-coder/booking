<?php

namespace App\Http\Requests\BillingAddress;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBillingAddressRequest extends FormRequest
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
            'first_name',
            'last_name',
            'user_id' => 'required',
            'country_id' => 'required',
            'email',
            'phone_number',
            'state_id' => 'required',
            'full_address' => 'required',
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
