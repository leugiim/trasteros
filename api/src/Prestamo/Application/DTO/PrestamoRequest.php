<?php

declare(strict_types=1);

namespace App\Prestamo\Application\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class PrestamoRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'El local es obligatorio')]
        #[Assert\Positive(message: 'El ID de local debe ser un número positivo')]
        public int $localId = 0,

        #[Assert\Length(max: 255, maxMessage: 'La entidad bancaria no puede superar los 255 caracteres')]
        public ?string $entidadBancaria = null,

        #[Assert\Length(max: 100, maxMessage: 'El número de préstamo no puede superar los 100 caracteres')]
        public ?string $numeroPrestamo = null,

        #[Assert\NotBlank(message: 'El capital solicitado es obligatorio')]
        #[Assert\Positive(message: 'El capital solicitado debe ser un número positivo')]
        public float $capitalSolicitado = 0.0,

        #[Assert\NotBlank(message: 'El total a devolver es obligatorio')]
        #[Assert\Positive(message: 'El total a devolver debe ser un número positivo')]
        public float $totalADevolver = 0.0,

        #[Assert\PositiveOrZero(message: 'El tipo de interés debe ser un número positivo o cero')]
        public ?float $tipoInteres = null,

        #[Assert\NotBlank(message: 'La fecha de concesión es obligatoria')]
        #[Assert\Date(message: 'La fecha de concesión debe ser una fecha válida')]
        public string $fechaConcesion = '',

        #[Assert\Choice(
            choices: ['activo', 'cancelado', 'finalizado'],
            message: 'El estado debe ser uno de: activo, cancelado, finalizado'
        )]
        public string $estado = 'activo'
    ) {
    }
}
