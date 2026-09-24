<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exception;

/**
 * HTTP 403. The caller is authenticated but not allowed (e.g. an inactive user).
 */
abstract class ForbiddenError extends DomainError
{
}
