<?php

declare(strict_types=1);

namespace App\Contrato\Domain\Exception;

use App\Shared\Domain\Exception\ValidationError;

final class InvalidFianzaException extends ValidationError
{

    public function field(): string
    {
        return 'fianza';
    }
}
