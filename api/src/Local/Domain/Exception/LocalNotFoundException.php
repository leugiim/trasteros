<?php

declare(strict_types=1);

namespace App\Local\Domain\Exception;

use App\Shared\Domain\Exception\NotFoundError;

final class LocalNotFoundException extends NotFoundError
{
    public static function withId(int $id): self
    {
        return new self(sprintf('Local with id "%d" not found', $id));
    }

    public function errorCode(): string
    {
        return 'LOCAL_NOT_FOUND';
    }
}
