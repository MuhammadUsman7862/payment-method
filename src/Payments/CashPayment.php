<?php

declare(strict_types=1);

namespace App\Payments;

use App\Exceptions\PaymentFailedException;

class CashPayment extends AbstractPayment
{
    public function __construct(private float $cashTendered)
    {
    }

    public function getName(): string
    {
        return 'cash';
    }

    protected function execute(float $amount): void
    {
        if ($this->cashTendered < $amount) {
            throw PaymentFailedException::because(sprintf(
                'cash given (%.2f) is less than the amount due (%.2f).',
                $this->cashTendered,
                $amount
            ));
        }
    }
}
