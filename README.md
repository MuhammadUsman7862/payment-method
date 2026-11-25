# Payment Processing App (PHP, OOP)

## Run
```
composer install      # or: composer dump-autoload
php index.php                      # command-line demo
php -S localhost:8000 -t public    # web page with CSS
```
Requires PHP 8.1+. For the web page, open http://localhost:8000 in your browser.

## Structure
```
src/
  Contracts/   PaymentInterface
  Exceptions/  PaymentException (base), InvalidPaymentAmountException,
               UnsupportedPaymentMethodException, PaymentFailedException
  Payments/    AbstractPayment, CashPayment, CreditCardPayment,
               BankTransferPayment, PayPalPayment (the "new method" example)
  Services/    PaymentService, PaymentMethodRegistry
index.php      Command-line demo
public/        Web page: index.php (form) and style.css
```
`````````
## How to add a new payment method
1. Create a class that extends `AbstractPayment` (see `PayPalPayment`).
2. Implement `getName()` and `execute()`.
3. Register it: `$registry->register('paypal', new PayPalPayment(...));`

`PaymentService` and all other payment classes stay unchanged.
