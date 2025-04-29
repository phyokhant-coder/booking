<?php

namespace App\Http\Requests\Payment;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePaymentRequest extends FormRequest
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
            'order_id' => 'required',
            'payment_method_id' => 'required',
            'image_url' => 'required'
        ];
    }

    public function messages(): array
    {
        return [
            'order_id.required' => 'The order id field is required.',
            'payment_method_id.required' => 'The payment method id field is required.',
            'image_url.required' => 'The screenshot image field is required.',
        ];
    }
}
