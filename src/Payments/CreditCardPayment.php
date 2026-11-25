<?php

declare(strict_types=1);

namespace App\Payments;

use App\Exceptions\PaymentFailedException;

class CreditCardPayment extends AbstractPayment
{
    public function __construct(
        private string $cardNumber,
        private string $cardHolder,
        private string $expiry // format: MM/YY
    ) {
    }

    public function getName(): string
    {
        return 'credit card';
    }

    protected function execute(float $amount): void
    {
        $number = preg_replace('/\D/', '', $this->cardNumber);

        if (!$this->passesLuhnCheck($number)) {
            throw PaymentFailedException::because('card number is not valid.');
        }
        if ($this->isExpired()) {
            throw PaymentFailedException::because('card has expired.');
        }
    }

    /** Standard Luhn checksum used to detect mistyped card numbers. */
    private function passesLuhnCheck(string $number): bool
    {
        $length = strlen($number);
        if ($length < 13 || $length > 19) {
            return false;
        }

        $sum = 0;
        $alt = false;
        for ($i = $length - 1; $i >= 0; $i--) {
            $digit = (int) $number[$i];
            if ($alt) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            $sum += $digit;
            $alt = !$alt;
        }
        return $sum % 10 === 0;
    }

    private function isExpired(): bool
    {
        if (!preg_match('#^(0[1-9]|1[0-2])/(\d{2})$#', $this->expiry, $m)) {
            return true;
        }
        $lastDay = new \DateTimeImmutable(sprintf('20%s-%s-01 23:59:59', $m[2], $m[1]));
        $lastDay = $lastDay->modify('last day of this month');

        return $lastDay < new \DateTimeImmutable();
    }
}
