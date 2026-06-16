<?php

declare(strict_types=1);

namespace App\Exceptions\Invoice;

use App\Exceptions\AppException;

final class InvoiceRenderException extends AppException
{
    public function __construct(string $message = 'Unable to generate the invoice PDF.', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
