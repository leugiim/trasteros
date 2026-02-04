<?php

declare(strict_types=1);

namespace App\Contrato\Domain\Exception;

final class ContratoNotFoundException extends \DomainException
{
    public static function withId(int $id): self
    {
        return new self(sprintf('Contrato with id %d not found', $id));
    }
}
