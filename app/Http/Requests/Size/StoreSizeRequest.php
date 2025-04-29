<?php

namespace App\Http\Requests\Size;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSizeRequest extends FormRequest
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
            'code' => 'required|min:4',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'code.required' => 'The code field is required.',
        ];
    }
}
