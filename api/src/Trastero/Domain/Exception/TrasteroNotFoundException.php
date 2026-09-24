<?php

declare(strict_types=1);

namespace App\Trastero\Domain\Exception;

use App\Shared\Domain\Exception\NotFoundError;

final class TrasteroNotFoundException extends NotFoundError
{
    public static function withId(int $id): self
    {
        return new self(sprintf('Trastero with id %d not found', $id));
    }

    public static function withNumeroAndLocal(string $numero, int $localId): self
    {
        return new self(sprintf('Trastero with numero %s in local %d not found', $numero, $localId));
    }

    public function errorCode(): string
    {
        return 'TRASTERO_NOT_FOUND';
    }
}
