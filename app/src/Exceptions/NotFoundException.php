<?php

namespace App\Exceptions;

class NotFoundException extends AppException
{
    protected int $httpCode = 404;

    public function __construct(string $message = 'Not found')
    {
        parent::__construct($message);
    }
}
