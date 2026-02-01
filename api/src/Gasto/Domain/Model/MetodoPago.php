<?php

declare(strict_types=1);

namespace App\Gasto\Domain\Model;

enum MetodoPago: string
{
    case EFECTIVO = 'efectivo';
    case TRANSFERENCIA = 'transferencia';
    case TARJETA = 'tarjeta';
    case DOMICILIACION = 'domiciliacion';

    public function label(): string
    {
        return match($this) {
            self::EFECTIVO => 'Efectivo',
            self::TRANSFERENCIA => 'Transferencia',
            self::TARJETA => 'Tarjeta',
            self::DOMICILIACION => 'Domiciliación',
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
