<?php

declare(strict_types=1);

namespace App\Trastero\Domain\Model;

enum TrasteroEstado: string
{
    case DISPONIBLE = 'disponible';
    case OCUPADO = 'ocupado';
    case MANTENIMIENTO = 'mantenimiento';
    case RESERVADO = 'reservado';

    public function label(): string
    {
        return match($this) {
            self::DISPONIBLE => 'Disponible',
            self::OCUPADO => 'Ocupado',
            self::MANTENIMIENTO => 'Mantenimiento',
            self::RESERVADO => 'Reservado',
        };
    }

    public function isAvailable(): bool
    {
        return $this === self::DISPONIBLE;
    }

    public function isOccupied(): bool
    {
        return $this === self::OCUPADO;
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
