<?php

declare(strict_types=1);

namespace App\Prestamo\Domain\Model;

enum PrestamoEstado: string
{
    case ACTIVO = 'activo';
    case CANCELADO = 'cancelado';
    case FINALIZADO = 'finalizado';

    public function label(): string
    {
        return match($this) {
            self::ACTIVO => 'Activo',
            self::CANCELADO => 'Cancelado',
            self::FINALIZADO => 'Finalizado',
        };
    }

    public static function fromString(string $value): self
    {
        return self::from($value);
    }

    public static function tryFromString(?string $value): ?self
    {
        if ($value === null) {
            return null;
        }

        return self::tryFrom($value);
    }
}
