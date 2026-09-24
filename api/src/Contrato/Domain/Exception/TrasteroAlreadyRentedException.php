<?php

declare(strict_types=1);

namespace App\Contrato\Domain\Exception;

use App\Shared\Domain\Exception\ConflictError;

final class TrasteroAlreadyRentedException extends ConflictError
{
    public function __construct(int $trasteroId)
    {
        parent::__construct(sprintf('El trastero con id %d ya tiene un contrato activo', $trasteroId));
    }

    public function errorCode(): string
    {
        return 'TRASTERO_ALREADY_RENTED';
    }
}
