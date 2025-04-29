<?php

namespace App\Api\Foundation\Validation;

use App\Api\Foundation\Exceptions\ValidationException;
use Illuminate\Support\Facades\Validator as LaravelValidator;

class Validator extends LaravelValidator
{
    /**
     * @param array $data
     * @param array $rules
     * @param array $messages
     * @return bool
     * @throws ValidationException
     */
    public function validate(array $data, array $rules, array $messages = []): bool
    {
        $validation = LaravelValidator::make($data, $rules, $messages);

        if ($validation->fails()) {
            throw new ValidationException($validation->errors());
        }

        return true;
    }
}
