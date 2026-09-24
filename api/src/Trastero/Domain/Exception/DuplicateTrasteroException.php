<?php

declare(strict_types=1);

namespace App\Trastero\Domain\Exception;

use App\Shared\Domain\Exception\ConflictError;

final class DuplicateTrasteroException extends ConflictError
{
    public static function withNumeroAndLocal(string $numero, int $localId): self
    {
        return new self(sprintf(
            'Ya existe un trastero con el número %s en el local %d',
            $numero,
            $localId
        ));
    }

    public function errorCode(): string
    {
        return 'TRASTERO_ALREADY_EXISTS';
    }
}
