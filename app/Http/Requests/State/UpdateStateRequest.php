<?php

namespace App\Http\Requests\State;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStateRequest extends FormRequest
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
            'country_id' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'country_id.required' => 'The country field is required.',
        ];
    }
}
