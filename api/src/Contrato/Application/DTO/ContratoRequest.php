<?php

declare(strict_types=1);

namespace App\Contrato\Application\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class ContratoRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'El ID del trastero es obligatorio')]
        #[Assert\Positive(message: 'El ID del trastero debe ser positivo')]
        public int $trasteroId,

        #[Assert\NotBlank(message: 'El ID del cliente es obligatorio')]
        #[Assert\Positive(message: 'El ID del cliente debe ser positivo')]
        public int $clienteId,

        #[Assert\NotBlank(message: 'La fecha de inicio es obligatoria')]
        #[Assert\Type(type: 'string', message: 'La fecha de inicio debe ser una cadena')]
        public string $fechaInicio,

        public ?string $fechaFin = null,

        #[Assert\NotBlank(message: 'El precio mensual es obligatorio')]
        #[Assert\Positive(message: 'El precio mensual debe ser positivo')]
        #[Assert\Type(type: 'float', message: 'El precio mensual debe ser un número')]
        public float $precioMensual,

        #[Assert\PositiveOrZero(message: 'La fianza debe ser positiva o cero')]
        #[Assert\Type(type: 'float', message: 'La fianza debe ser un número')]
        public ?float $fianza = null,

        #[Assert\Type(type: 'bool', message: 'El campo fianzaPagada debe ser booleano')]
        public bool $fianzaPagada = false,

        #[Assert\Choice(
            choices: ['activo', 'finalizado', 'cancelado', 'pendiente'],
            message: 'El estado debe ser uno de: activo, finalizado, cancelado, pendiente'
        )]
        public string $estado = 'activo'
    ) {
    }
}
