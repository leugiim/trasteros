<?php

declare(strict_types=1);

namespace App\Cliente\Domain\Exception;

use App\Shared\Domain\Exception\ConflictError;

final class DuplicatedDniNieException extends ConflictError
{
    public static function withDniNie(string $dniNie): self
    {
        return new self(sprintf('Ya existe un cliente con el DNI/NIE %s', $dniNie));
    }

    public function errorCode(): string
    {
        // Kept as the API has always returned it (tests and clients rely on it)
        return 'ALREADY_EXISTS';
    }
}
