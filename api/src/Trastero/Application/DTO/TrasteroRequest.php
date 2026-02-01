<?php

declare(strict_types=1);

namespace App\Trastero\Application\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class TrasteroRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'El local es obligatorio')]
        #[Assert\Positive(message: 'El ID de local debe ser un número positivo')]
        public int $localId = 0,

        #[Assert\NotBlank(message: 'El número de trastero es obligatorio')]
        #[Assert\Length(max: 20, maxMessage: 'El número no puede superar los 20 caracteres')]
        public string $numero = '',

        #[Assert\Length(max: 100, maxMessage: 'El nombre no puede superar los 100 caracteres')]
        public ?string $nombre = null,

        #[Assert\NotBlank(message: 'La superficie es obligatoria')]
        #[Assert\Positive(message: 'La superficie debe ser un número positivo')]
        #[Assert\LessThanOrEqual(value: 9999.99, message: 'La superficie no puede superar 9999.99 m²')]
        public float $superficie = 0.0,

        #[Assert\NotBlank(message: 'El precio mensual es obligatorio')]
        #[Assert\PositiveOrZero(message: 'El precio mensual debe ser un número positivo o cero')]
        #[Assert\LessThanOrEqual(value: 99999999.99, message: 'El precio mensual no puede superar 99999999.99')]
        public float $precioMensual = 0.0,

        #[Assert\Choice(
            choices: ['disponible', 'ocupado', 'mantenimiento', 'reservado'],
            message: 'El estado debe ser uno de: disponible, ocupado, mantenimiento, reservado'
        )]
        public string $estado = 'disponible'
    ) {
    }
}
