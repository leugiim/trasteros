<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exception;

/**
 * HTTP 404. A requested resource doesn't exist.
 */
abstract class NotFoundError extends DomainError
{
}
