<?php

declare(strict_types=1);

namespace App\Local\Application\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class LocalRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'El nombre es obligatorio')]
        #[Assert\Length(max: 255, maxMessage: 'El nombre no puede superar los 255 caracteres')]
        public string $nombre = '',

        #[Assert\NotBlank(message: 'La dirección es obligatoria')]
        #[Assert\Positive(message: 'El ID de dirección debe ser un número positivo')]
        public int $direccionId = 0,

        #[Assert\Positive(message: 'La superficie total debe ser un número positivo')]
        public ?float $superficieTotal = null,

        #[Assert\Positive(message: 'El número de trasteros debe ser un número positivo')]
        public ?int $numeroTrasteros = null,

        #[Assert\Date(message: 'La fecha de compra debe ser una fecha válida')]
        public ?string $fechaCompra = null,

        #[Assert\Positive(message: 'El precio de compra debe ser un número positivo')]
        public ?float $precioCompra = null,

        #[Assert\Length(max: 50, maxMessage: 'La referencia catastral no puede superar los 50 caracteres')]
        public ?string $referenciaCatastral = null,

        #[Assert\Positive(message: 'El valor catastral debe ser un número positivo')]
        public ?float $valorCatastral = null
    ) {
    }
}
