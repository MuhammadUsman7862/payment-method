<?php

declare(strict_types=1);

namespace App\Exceptions;

use Throwable;

class PaymentFailedException extends PaymentException
{
    public static function because(string $reason, ?Throwable $previous = null): self
    {
        return new self('Payment failed: ' . $reason, 0, $previous);
    }
}
