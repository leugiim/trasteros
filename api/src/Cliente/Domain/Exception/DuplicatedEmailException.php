<?php

declare(strict_types=1);

namespace App\Cliente\Domain\Exception;

use App\Shared\Domain\Exception\ConflictError;

final class DuplicatedEmailException extends ConflictError
{
    public static function withEmail(string $email): self
    {
        return new self(sprintf('Ya existe un cliente con el email %s', $email));
    }

    public function errorCode(): string
    {
        // Kept as the API has always returned it (documented as a field conflict)
        return 'VALIDATION_ERROR';
    }
}
