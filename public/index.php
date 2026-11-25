<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Exceptions\InvalidPaymentAmountException;
use App\Exceptions\PaymentException;
use App\Exceptions\UnsupportedPaymentMethodException;
use App\Payments\BankTransferPayment;
use App\Payments\CashPayment;
use App\Payments\CreditCardPayment;
use App\Payments\PayPalPayment;
use App\Services\PaymentService;

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function old(string $key, string $default = ''): string
{
    return e((string) ($_POST[$key] ?? $default));
}

function num(string $key): float
{
    return is_numeric($_POST[$key] ?? null) ? (float) $_POST[$key] : 0.0;
}

// Each entry builds a payment object from the submitted form data.
// To support a new method here, add one more line.
$factories = [
    'cash'   => fn () => new CashPayment(num('cash_given')),
    'card'   => fn () => new CreditCardPayment(
        (string) ($_POST['card_number'] ?? ''),
        (string) ($_POST['card_holder'] ?? ''),
        (string) ($_POST['card_expiry'] ?? '')
    ),
    'bank'   => fn () => new BankTransferPayment((string) ($_POST['account_number'] ?? ''), num('account_balance')),
    'paypal' => fn () => new PayPalPayment((string) ($_POST['paypal_email'] ?? '')),
];

$labels = [
    'cash'   => 'Cash',
    'card'   => 'Credit Card',
    'bank'   => 'Bank Transfer',
    'paypal' => 'PayPal',
];

$result = null;
$error = null;
$errorType = null;
$selected = (string) ($_POST['method'] ?? 'cash');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!isset($factories[$selected])) {
            throw UnsupportedPaymentMethodException::forMethod($selected, array_keys($factories));
        }

        $rawAmount = $_POST['amount'] ?? '';
        if (!is_numeric($rawAmount)) {
            throw InvalidPaymentAmountException::notANumber();
        }

        $service = new PaymentService($factories[$selected]());
        $result = $service->pay((float) $rawAmount);
    } catch (PaymentException $ex) {
        $error = $ex->getMessage();
        $errorType = (new ReflectionClass($ex))->getShortName();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Processor</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="card">
    <h1>Payment Processor</h1>
    <p class="subtitle">Choose a payment method and enter an amount.</p>

    <?php if ($result): ?>
        <div class="message success"><?= e($result) ?></div>
    <?php elseif ($error): ?>
        <div class="message error">
            <?= e($error) ?>
            <small><?= e($errorType) ?></small>
        </div>
    <?php endif; ?>

    <form method="post" action="">
        <label for="method">Payment method</label>
        <select id="method" name="method">
            <?php foreach ($labels as $key => $label): ?>
                <option value="<?= e($key) ?>" <?= $selected === $key ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="amount">Amount</label>
        <input id="amount" name="amount" type="text" inputmode="decimal" placeholder="e.g. 150.00" value="<?= old('amount') ?>">

        <div class="method-fields" data-method="cash">
            <label for="cash_given">Cash given</label>
            <input id="cash_given" name="cash_given" type="text" inputmode="decimal" placeholder="e.g. 200" value="<?= old('cash_given') ?>">
        </div>

        <div class="method-fields" data-method="card">
            <label for="card_number">Card number</label>
            <input id="card_number" name="card_number" type="text" inputmode="numeric" placeholder="4539 1488 0343 6467" value="<?= old('card_number') ?>">
            <label for="card_holder">Card holder</label>
            <input id="card_holder" name="card_holder" type="text" placeholder="Muhammad Usman" value="<?= old('card_holder') ?>">
            <div class="row">
                <div>
                    <label for="card_expiry">Expiry (MM/YY)</label>
                    <input id="card_expiry" name="card_expiry" type="text" placeholder="12/30" value="<?= old('card_expiry') ?>">
                </div>
            </div>
        </div>

        <div class="method-fields" data-method="bank">
            <label for="account_number">Account number</label>
            <input id="account_number" name="account_number" type="text" placeholder="PK36SCBL0000001123456702" value="<?= old('account_number') ?>">
            <label for="account_balance">Account balance</label>
            <input id="account_balance" name="account_balance" type="text" inputmode="decimal" placeholder="e.g. 5000" value="<?= old('account_balance') ?>">
        </div>

        <div class="method-fields" data-method="paypal">
            <label for="paypal_email">PayPal email</label>
            <input id="paypal_email" name="paypal_email" type="text" placeholder="name@example.com" value="<?= old('paypal_email') ?>">
        </div>

        <button type="submit">Pay now</button>
    </form>
</div>

<script>
    // Show only the fields for the selected payment method
    const select = document.getElementById('method');
    function showFields() {
        document.querySelectorAll('.method-fields').forEach(function (box) {
            box.classList.toggle('active', box.dataset.method === select.value);
        });
    }
    select.addEventListener('change', showFields);
    showFields();
</script>
</body>
</html>
