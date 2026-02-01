<?php

declare(strict_types=1);

namespace App\Gasto\Domain\Model;

enum GastoCategoria: string
{
    case SUMINISTROS = 'suministros';
    case SEGUROS = 'seguros';
    case IMPUESTOS = 'impuestos';
    case MANTENIMIENTO = 'mantenimiento';
    case PRESTAMO = 'prestamo';
    case GESTORIA = 'gestoria';
    case OTROS = 'otros';

    public function label(): string
    {
        return match($this) {
            self::SUMINISTROS => 'Suministros',
            self::SEGUROS => 'Seguros',
            self::IMPUESTOS => 'Impuestos',
            self::MANTENIMIENTO => 'Mantenimiento',
            self::PRESTAMO => 'Préstamo',
            self::GESTORIA => 'Gestoría',
            self::OTROS => 'Otros',
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
