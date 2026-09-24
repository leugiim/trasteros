<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exception;

/**
 * HTTP 409. The operation conflicts with existing data (duplicates, a trastero that's already rented...).
 */
abstract class ConflictError extends DomainError
{
}
