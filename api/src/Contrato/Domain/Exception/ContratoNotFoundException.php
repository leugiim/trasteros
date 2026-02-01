<?php

declare(strict_types=1);

namespace App\Contrato\Domain\Exception;

final class ContratoNotFoundException extends \DomainException
{
    public function __construct(int $id)
    {
        parent::__construct(sprintf('Contrato with id %d not found', $id));
    }
}
