<?php

namespace App\Api\Foundation\Exceptions;

use Exception;

class DuplicateEntryException extends Exception
{
    protected $code = 409;
}
