<?php

declare(strict_types=1);

namespace App\Contracts;

/**
 * Contract that every payment method must follow.
 */
interface PaymentInterface
{
    /**
     * Process a payment and return a confirmation message.
     *
     * @throws \App\Exceptions\InvalidPaymentAmountException
     * @throws \App\Exceptions\PaymentFailedException
     */
    public function processPayment(float $amount): string;

    /** Short name of the payment method, e.g. "cash". */
    public function getName(): string;
}
