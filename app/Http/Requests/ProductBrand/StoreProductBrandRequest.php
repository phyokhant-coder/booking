<?php

namespace App\Http\Requests\ProductBrand;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreProductBrandRequest extends FormRequest
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
            'name' => 'required|min:2',
            'image_url' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'description' => 'nullable',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'image_url.mimes' => 'The image url must be a file of type: jpeg, png or jpg.',
            'image_url.max' => 'The image must not be larger than 5MB.'
        ];
    }
}
