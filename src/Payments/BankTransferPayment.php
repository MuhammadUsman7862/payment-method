<?php

declare(strict_types=1);

namespace App\Payments;

use App\Exceptions\PaymentFailedException;

class BankTransferPayment extends AbstractPayment
{
    public function __construct(
        private string $accountNumber,
        private float $accountBalance
    ) {
    }

    public function getName(): string
    {
        return 'bank transfer';
    }

    protected function execute(float $amount): void
    {
        if (trim($this->accountNumber) === '') {
            throw PaymentFailedException::because('bank account number is missing.');
        }
        if ($this->accountBalance < $amount) {
            throw PaymentFailedException::because('insufficient funds in the bank account.');
        }
        $this->accountBalance -= $amount;
    }
}
