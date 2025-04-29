<?php

namespace App\Api\Foundation\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotFoundResourceException extends Exception
{
    protected $code = 404;

    protected $message = 'The requested resource ID does not exist.';

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
            'message' => $this->message,
        ], $this->code);
    }
}
