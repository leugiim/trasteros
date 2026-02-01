<?php

declare(strict_types=1);

namespace App\Direccion\Domain\Model;

use App\Direccion\Domain\Exception\InvalidCoordenadasException;

final readonly class Coordenadas
{
    private function __construct(
        public ?float $latitud,
        public ?float $longitud
    ) {
    }

    public static function create(?float $latitud, ?float $longitud): self
    {
        if (($latitud === null && $longitud !== null) || ($latitud !== null && $longitud === null)) {
            throw InvalidCoordenadasException::incomplete();
        }

        if ($latitud !== null && ($latitud < -90 || $latitud > 90)) {
            throw InvalidCoordenadasException::invalidLatitud($latitud);
        }

        if ($longitud !== null && ($longitud < -180 || $longitud > 180)) {
            throw InvalidCoordenadasException::invalidLongitud($longitud);
        }

        return new self($latitud, $longitud);
    }

    public static function empty(): self
    {
        return new self(null, null);
    }

    public function isEmpty(): bool
    {
        return $this->latitud === null && $this->longitud === null;
    }

    public function equals(Coordenadas $other): bool
    {
        return $this->latitud === $other->latitud && $this->longitud === $other->longitud;
    }
}
