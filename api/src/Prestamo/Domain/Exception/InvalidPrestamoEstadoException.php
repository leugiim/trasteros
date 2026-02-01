<?php

declare(strict_types=1);

namespace App\Prestamo\Domain\Exception;

final class InvalidPrestamoEstadoException extends \InvalidArgumentException
{
    public static function withValue(string $value): self
    {
        return new self(sprintf('Estado de préstamo inválido: %s. Valores permitidos: activo, cancelado, finalizado', $value));
    }
}
