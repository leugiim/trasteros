<?php

declare(strict_types=1);

namespace App\Ingreso\Domain\Model;

enum IngresoCategoria: string
{
    case MENSUALIDAD = 'mensualidad';
    case FIANZA = 'fianza';
    case PENALIZACION = 'penalizacion';
    case OTROS = 'otros';

    public function label(): string
    {
        return match($this) {
            self::MENSUALIDAD => 'Mensualidad',
            self::FIANZA => 'Fianza',
            self::PENALIZACION => 'Penalización',
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
