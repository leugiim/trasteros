<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exception;

/**
 * Base class for business-rule errors. Each one declares the stable,
 * machine-readable code the API returns (e.g. CLIENTE_NOT_FOUND); its type
 * (the subclasses below) decides the HTTP status. See
 * Shared\Infrastructure\EventSubscriber\MessengerExceptionSubscriber.
 */
abstract class DomainError extends \DomainException
{
    abstract public function errorCode(): string;
}
