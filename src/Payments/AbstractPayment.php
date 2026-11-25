<?php

declare(strict_types=1);

namespace App\Payments;

use App\Contracts\PaymentInterface;
use App\Exceptions\InvalidPaymentAmountException;

/**
 * Shared functionality for all payment methods.
 *
 * processPayment() is a "template method": it validates the amount,
 * creates a transaction ID, then calls execute(), which each child class
 * implements in its own way.
 */
abstract class AbstractPayment implements PaymentInterface
{
    protected const MIN_AMOUNT = 1.00;
    protected const MAX_AMOUNT = 1_000_000.00;

    final public function processPayment(float $amount): string
    {
        $this->validateAmount($amount);

        $transactionId = $this->generateTransactionId();
        $this->execute($amount);

        return sprintf(
            '[%s] %s payment of %.2f processed successfully.',
            $transactionId,
            $this->getName(),
            $amount
        );
    }

    /** Each payment method puts its own logic here. */
    abstract protected function execute(float $amount): void;

    protected function validateAmount(float $amount): void
    {
        if (is_nan($amount) || is_infinite($amount)) {
            throw InvalidPaymentAmountException::notANumber();
        }
        if ($amount <= 0) {
            throw InvalidPaymentAmountException::notPositive($amount);
        }
        if ($amount < static::MIN_AMOUNT) {
            throw InvalidPaymentAmountException::belowMinimum($amount, static::MIN_AMOUNT);
        }
        if ($amount > static::MAX_AMOUNT) {
            throw InvalidPaymentAmountException::aboveMaximum($amount, static::MAX_AMOUNT);
        }
    }

    protected function generateTransactionId(): string
    {
        return strtoupper(substr($this->getName(), 0, 3)) . '-' . strtoupper(bin2hex(random_bytes(4)));
    }
}
