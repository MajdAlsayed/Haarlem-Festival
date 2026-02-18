<?php

namespace App\Exceptions;

class ValidationException extends AppException
{
    protected int $httpCode = 400;

    /** @param array<string, string> $errors */
    public function __construct(string $message = 'Validation failed', private array $errors = [])
    {
        parent::__construct($message);
    }

    /** @return array<string, string> */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
