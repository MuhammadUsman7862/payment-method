<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use App\Exceptions\PaymentException;
use App\Payments\BankTransferPayment;
use App\Payments\CashPayment;
use App\Payments\CreditCardPayment;
use App\Payments\PayPalPayment;
use App\Services\PaymentMethodRegistry;
use App\Services\PaymentService;

function run(string $title, callable $action): void
{
    echo "== $title\n";
    try {
        echo '   OK:    ' . $action() . "\n";
    } catch (PaymentException $e) {
        echo '   ERROR: ' . get_class($e) . ' -> ' . $e->getMessage() . "\n";
    }
    echo "\n";
}

// Register the supported methods
$registry = (new PaymentMethodRegistry())
    ->register('cash', new CashPayment(cashTendered: 200.00))
    ->register('card', new CreditCardPayment('4539 1488 0343 6467', 'Muhammad Usman', '12/30'))
    ->register('bank', new BankTransferPayment('PK36SCBL0000001123456702', 5000.00));

// 1) Polymorphism: same service code, different payment methods
foreach (['cash', 'card', 'bank'] as $method) {
    run("Pay 150.00 with $method", fn () => (new PaymentService($registry->get($method)))->pay(150.00));
}

// 2) Invalid amounts
run('Negative amount', fn () => (new PaymentService($registry->get('cash')))->pay(-20));
run('Zero amount',     fn () => (new PaymentService($registry->get('card')))->pay(0));
run('Too large',       fn () => (new PaymentService($registry->get('bank')))->pay(5_000_000));

// 3) Unsupported method
run('Unsupported method (bitcoin)', fn () => (new PaymentService($registry->get('bitcoin')))->pay(50));

// 4) Failed payments
run('Cash: not enough given',  fn () => (new PaymentService(new CashPayment(10.00)))->pay(100));
run('Card: bad card number',   fn () => (new PaymentService(new CreditCardPayment('1234 5678 9012 3456', 'Test', '12/30')))->pay(100));
run('Bank: insufficient funds', fn () => (new PaymentService(new BankTransferPayment('PK123', 20.00)))->pay(100));

// 5) NEW payment method added - PaymentService was NOT changed
$registry->register('paypal', new PayPalPayment('usman@example.com'));
run('NEW method: PayPal', fn () => (new PaymentService($registry->get('paypal')))->pay(75.50));

echo 'Supported methods now: ' . implode(', ', $registry->supported()) . "\n";
