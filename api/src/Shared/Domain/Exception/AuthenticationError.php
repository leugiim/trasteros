<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exception;

/**
 * HTTP 401. The caller couldn't be authenticated (bad credentials, invalid or expired token).
 */
abstract class AuthenticationError extends DomainError
{
}
