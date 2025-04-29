<?php

namespace App\Http\Requests\Payment;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
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
            'order_id' => 'required|exists:orders,id',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'screenshot_image_url' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'order_id.required' => 'The order id field is required.',
            'payment_method_id.required' => 'The payment method id field is required.',
            'screenshot_image_url.mimes' => 'The image url must be a file of type: jpeg, png or jpg.',
            'screenshot_image_url.max' => 'The image must not be larger than 5MB.'
        ];
    }
}
