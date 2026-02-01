<?php

declare(strict_types=1);

namespace App\Gasto\Domain\Exception;

final class GastoNotFoundException extends \DomainException
{
    public static function withId(int $id): self
    {
        return new self(sprintf('Gasto with id %d not found', $id));
    }
}
