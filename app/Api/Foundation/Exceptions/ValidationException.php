<?php

namespace App\Api\Foundation\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ValidationException extends Exception
{
    protected $code = 400;
    protected mixed $errors;

    public function __construct($message = 'Validation failed.', $errors = [], $code = 400)
    {
        parent::__construct($message, $code);
        $this->errors = $errors;
    }

    /**
     * Render the exception as a JSON response.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'errors' => $this->errors,
        ], $this->code);
    }
}
