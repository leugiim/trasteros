<?php

declare(strict_types=1);

namespace App\Prestamo\Domain\Exception;

use App\Shared\Domain\Exception\ValidationError;

final class InvalidPrestamoEstadoException extends ValidationError
{
    public static function withValue(string $value): self
    {
        return new self(sprintf('Estado de préstamo inválido: %s. Valores permitidos: activo, cancelado, finalizado', $value));
    }

    public function field(): string
    {
        return 'prestamoEstado';
    }
}
