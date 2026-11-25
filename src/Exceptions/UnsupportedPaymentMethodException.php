<?php

declare(strict_types=1);

namespace App\Exceptions;

class UnsupportedPaymentMethodException extends PaymentException
{
    public static function forMethod(string $method, array $supported): self
    {
        return new self(sprintf(
            'Unsupported payment method "%s". Supported methods: %s.',
            $method,
            $supported ? implode(', ', $supported) : 'none'
        ));
    }
}
