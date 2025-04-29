<?php

namespace App\Http\Requests\Order;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
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
            'user_id' => ['nullable', 'integer'],
            'total_amount' => ['nullable', 'numeric'],
            'order_note' => ['nullable', 'string'],
            'order_date' => ['nullable', 'date'],
            'billing_address_id' => ['nullable', 'integer'],
            'promo_code_id' => ['nullable', 'integer', 'exists:promo_codes,id'],

            'products' => ['nullable', 'array'],
            'products.*.id' => ['required', 'integer', 'exists:products,id'],
            'products.*.product_variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'products.*.product_variant_detail_id' => ['required', 'integer', 'exists:product_variant_details,id'],
            'products.*.quantity' => ['required', 'integer', 'min:1'],
            'products.*.price' => ['required', 'numeric', 'min:0'],

            'payment_method_id' => ['nullable', 'exists:payment_methods,id'],
            'screenshot_image_url' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.integer' => 'The user ID must be an integer.',
            'total_amount.numeric' => 'The total amount must be a number.',
            'order_note.string' => 'The order note must be a string.'
        ];
    }
}
