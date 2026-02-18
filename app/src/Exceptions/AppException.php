<?php

namespace App\Exceptions;

use Exception;

class AppException extends Exception
{
    protected int $httpCode = 500;

    public function getHttpCode(): int
    {
        return $this->httpCode;
    }
}
