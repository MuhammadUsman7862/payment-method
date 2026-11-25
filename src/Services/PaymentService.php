<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\PaymentInterface;
use App\Exceptions\PaymentException;
use App\Exceptions\PaymentFailedException;
use Throwable;

/**
 * Works with ANY class that implements PaymentInterface.
 * It does not know about cash, cards, or bank transfers.
 */
class PaymentService
{
    public function __construct(private PaymentInterface $payment)
    {
    }

    public function pay(float $amount): string
    {
        try {
            return $this->payment->processPayment($amount);
        } catch (PaymentException $e) {
            throw $e; // our own exceptions pass through unchanged
        } catch (Throwable $e) {
            throw PaymentFailedException::because('unexpected error - ' . $e->getMessage(), $e);
        }
    }
}
