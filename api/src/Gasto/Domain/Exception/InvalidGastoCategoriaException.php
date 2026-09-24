<?php

declare(strict_types=1);

namespace App\Gasto\Domain\Exception;

use App\Shared\Domain\Exception\ValidationError;

final class InvalidGastoCategoriaException extends ValidationError
{
    public static function withValue(string $value): self
    {
        return new self(sprintf('Invalid gasto categoria: %s', $value));
    }

    public function field(): string
    {
        return 'gastoCategoria';
    }
}
