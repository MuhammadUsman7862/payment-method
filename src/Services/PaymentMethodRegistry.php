<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\PaymentInterface;
use App\Exceptions\UnsupportedPaymentMethodException;

/**
 * Looks up payment methods by name.
 * Adding a new method = one register() call.
 */
class PaymentMethodRegistry
{
    /** @var array<string, PaymentInterface> */
    private array $methods = [];

    public function register(string $name, PaymentInterface $method): self
    {
        $this->methods[strtolower($name)] = $method;
        return $this;
    }

    public function get(string $name): PaymentInterface
    {
        $key = strtolower($name);
        if (!isset($this->methods[$key])) {
            throw UnsupportedPaymentMethodException::forMethod($name, array_keys($this->methods));
        }
        return $this->methods[$key];
    }

    /** @return string[] */
    public function supported(): array
    {
        return array_keys($this->methods);
    }
}
