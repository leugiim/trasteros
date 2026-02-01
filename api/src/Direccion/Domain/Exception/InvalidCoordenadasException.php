<?php

declare(strict_types=1);

namespace App\Direccion\Domain\Exception;

final class InvalidCoordenadasException extends \DomainException
{
    public static function incomplete(): self
    {
        return new self('Las coordenadas deben incluir tanto latitud como longitud, o ninguna de las dos');
    }

    public static function invalidLatitud(float $latitud): self
    {
        return new self(sprintf('La latitud "%f" debe estar entre -90 y 90 grados', $latitud));
    }

    public static function invalidLongitud(float $longitud): self
    {
        return new self(sprintf('La longitud "%f" debe estar entre -180 y 180 grados', $longitud));
    }
}
