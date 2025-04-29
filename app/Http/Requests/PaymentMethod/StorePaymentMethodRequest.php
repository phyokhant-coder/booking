<?php

namespace App\Http\Requests\PaymentMethod;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePaymentMethodRequest extends FormRequest
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
            'name' => 'required',
            'account_name' => 'required',
            'account_number' => 'required',
            'image_url' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'account_name.required' => 'The account name field is required.',
            'account_number.required' => 'The account number field is required.',
            'image_url.mimes' => 'The image must be a file of type: jpeg, png or jpg.',
            'image_url.max' => 'The image must not be larger than 5MB.'
        ];
    }
}
