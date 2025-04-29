<?php

namespace App\Http\Requests\OrderLine;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreOrderLineRequest extends FormRequest
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
            'order_id' => ['nullable', 'integer'],
            'product_id' => ['nullable', 'integer'],
            'product_variant_id' => ['nullable', 'integer'],
            'product_variant_detail_id' => ['nullable', 'integer'],
            'quantity' => ['nullable', 'integer'],
            'price' => ['nullable', 'numeric'],
            'total_price' => ['nullable', 'numeric'],
        ];
    }

    public function messages(): array
    {
        return [
            'order_id.integer' => 'The order ID must be an integer.',
            'product_id.integer' => 'The product ID must be an integer.',
            'product_variant_id.integer' => 'The product variant ID must be an integer.',
            'quantity.integer' => 'The quantity must be an integer.',
            'price.numeric' => 'The price must be a numeric.',
            'total_price.numeric' => 'The total price must be a numeric.',
        ];
    }
}
