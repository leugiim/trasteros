<?php

declare(strict_types=1);

namespace App\Contrato\Domain\Exception;

final class TrasteroAlreadyRentedException extends \DomainException
{
    public function __construct(int $trasteroId)
    {
        parent::__construct(sprintf('El trastero con id %d ya tiene un contrato activo', $trasteroId));
    }
}
