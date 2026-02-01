<?php

declare(strict_types=1);

namespace App\Gasto\Domain\Exception;

final class InvalidGastoCategoriaException extends \DomainException
{
    public static function withValue(string $value): self
    {
        return new self(sprintf('Invalid gasto categoria: %s', $value));
    }
}
