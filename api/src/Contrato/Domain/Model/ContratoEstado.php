<?php

declare(strict_types=1);

namespace App\Contrato\Domain\Model;

enum ContratoEstado: string
{
    case ACTIVO = 'activo';
    case FINALIZADO = 'finalizado';
    case CANCELADO = 'cancelado';
    case PENDIENTE = 'pendiente';

    public function label(): string
    {
        return match($this) {
            self::ACTIVO => 'Activo',
            self::FINALIZADO => 'Finalizado',
            self::CANCELADO => 'Cancelado',
            self::PENDIENTE => 'Pendiente',
        };
    }

    public function isActivo(): bool
    {
        return $this === self::ACTIVO;
    }

    public function isFinalizado(): bool
    {
        return $this === self::FINALIZADO;
    }

    public function isCancelado(): bool
    {
        return $this === self::CANCELADO;
    }

    public function isPendiente(): bool
    {
        return $this === self::PENDIENTE;
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
