<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exception;

/**
 * HTTP 400 VALIDATION_ERROR: a value breaks a domain rule. The response's
 * `details` is keyed by field().
 */
abstract class ValidationError extends DomainError
{
    abstract public function field(): string;

    public function errorCode(): string
    {
        return 'VALIDATION_ERROR';
    }
}
