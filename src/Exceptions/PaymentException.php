<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

/**
 * Base class so all payment errors can be caught together.
 */
class PaymentException extends Exception
{
}
