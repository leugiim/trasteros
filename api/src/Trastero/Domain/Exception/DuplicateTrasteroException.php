<?php

declare(strict_types=1);

namespace App\Trastero\Domain\Exception;

final class DuplicateTrasteroException extends \DomainException
{
    public static function withNumeroAndLocal(string $numero, int $localId): self
    {
        return new self(sprintf(
            'Ya existe un trastero con el número %s en el local %d',
            $numero,
            $localId
        ));
    }
}
