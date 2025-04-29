<?php

namespace App\Api\Foundation\Exceptions;

use Exception;

class FatalErrorException extends Exception
{
    protected $message = 'Internal server error, Please try again later.';
}
