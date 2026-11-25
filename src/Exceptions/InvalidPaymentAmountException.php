<?php

declare(strict_types=1);

namespace App\Exceptions;

class InvalidPaymentAmountException extends PaymentException
{
    public static function notPositive(float $amount): self
    {
        return new self(sprintf('Invalid payment amount: %.2f. Amount must be greater than zero.', $amount));
    }

    public static function belowMinimum(float $amount, float $min): self
    {
        return new self(sprintf('Invalid payment amount: %.2f. Minimum allowed is %.2f.', $amount, $min));
    }

    public static function aboveMaximum(float $amount, float $max): self
    {
        return new self(sprintf('Invalid payment amount: %.2f. Maximum allowed is %.2f.', $amount, $max));
    }

    public static function notANumber(): self
    {
        return new self('Invalid payment amount: value is not a valid number.');
    }
}
