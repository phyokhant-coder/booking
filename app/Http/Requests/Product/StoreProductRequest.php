<?php

namespace App\Http\Requests\Product;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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
            'product_category_id' => 'nullable|exists:product_categories,id',
            'product_brand_id' => 'nullable|exists:product_brands,id',
            'image_url' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'nullable|in:unpublished,published',

            'product_images' => 'nullable|array',
            'product_images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:5120',

            'product_variants' => 'nullable|array',
            'product_variants.*.color' => 'required|string',

            'product_variants.*.product_variant_details' => 'nullable|array',
            'product_variants.*.product_variant_details.*.size' => 'required|string',
            'product_variants.*.product_variant_details.*.quantity' => 'required|integer|min:0',
            'product_variants.*.product_variant_details.*.price' => 'required|numeric|min:0',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'product_category_id.exists' => 'The selected category is invalid.',
            'product_brand_id.exists' => 'The selected brand is invalid.',

            'image_url.mimes' => 'The image must be a file of type: jpeg, png, or jpg.',
            'image_url.max' => 'The image must not be larger than 5MB.',

            'product_images.*.image' => 'Each product image must be a valid image file.',
            'product_images.*.mimes' => 'Each image must be of type: jpeg, png, jpg, gif, or svg.',
            'product_images.*.max' => 'Each image must not be larger than 5MB.',

            'name.required' => 'The product name is required.',
            'name.max' => 'The product name should not exceed 255 characters.',

            'price.required' => 'The price is required.',
            'price.numeric' => 'The price must be a valid number.',
            'price.min' => 'The price must be at least 0.',

            'status.in' => 'The status must be either unpublished or published.',

            // Product Variants
            'product_variants.*.color.required' => 'Each product variant must have a color.',

            // Product Variant Details
            'product_variants.*.product_variant_details.*.size.required' => 'Each variant detail must have a size.',
            'product_variants.*.product_variant_details.*.quantity.required' => 'Each variant detail must have a quantity.',
            'product_variants.*.product_variant_details.*.quantity.integer' => 'The quantity must be an integer.',
            'product_variants.*.product_variant_details.*.quantity.min' => 'The quantity must be at least 0.',
            'product_variants.*.product_variant_details.*.price.required' => 'Each variant detail must have a price.',
            'product_variants.*.product_variant_details.*.price.numeric' => 'The price must be a valid number.',
            'product_variants.*.product_variant_details.*.price.min' => 'The price must be at least 0.',
        ];
    }
}
