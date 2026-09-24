<?php

declare(strict_types=1);

namespace App\Trastero\Domain\Exception;

use App\Shared\Domain\Exception\ValidationError;

final class InvalidSuperficieException extends ValidationError
{
    public static function notPositive(float $value): self
    {
        return new self(sprintf('La superficie debe ser un valor positivo, se proporcionó: %.2f', $value));
    }

    public static function tooLarge(float $value): self
    {
        return new self(sprintf('La superficie no puede superar 9999.99 m², se proporcionó: %.2f', $value));
    }

    public function field(): string
    {
        return 'superficie';
    }
}
