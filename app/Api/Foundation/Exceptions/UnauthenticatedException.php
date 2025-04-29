<?php

namespace App\Api\Foundation\Exceptions;

use Exception;

class UnauthenticatedException extends Exception
{
    protected $code = 403;

    protected $message = 'Unauthenticated';
}
