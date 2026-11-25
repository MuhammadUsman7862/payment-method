<?php

declare(strict_types=1);

namespace App\Payments;

use App\Exceptions\PaymentFailedException;

/**
 * Example of a NEW payment method.
 * Nothing else in the project had to change to support it.
 */
class PayPalPayment extends AbstractPayment
{
    public function __construct(private string $email)
    {
    }

    public function getName(): string
    {
        return 'paypal';
    }

    protected function execute(float $amount): void
    {
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            throw PaymentFailedException::because('PayPal email address is not valid.');
        }
    }
}
