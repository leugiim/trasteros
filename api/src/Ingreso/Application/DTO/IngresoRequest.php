<?php

declare(strict_types=1);

namespace App\Ingreso\Application\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class IngresoRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'El contrato es obligatorio')]
        #[Assert\Positive(message: 'El ID de contrato debe ser un número positivo')]
        public int $contratoId = 0,

        #[Assert\NotBlank(message: 'El concepto es obligatorio')]
        #[Assert\Length(max: 255, maxMessage: 'El concepto no puede superar los 255 caracteres')]
        public string $concepto = '',

        #[Assert\NotBlank(message: 'El importe es obligatorio')]
        #[Assert\Positive(message: 'El importe debe ser un número positivo')]
        public float $importe = 0.0,

        #[Assert\NotBlank(message: 'La fecha de pago es obligatoria')]
        #[Assert\Date(message: 'La fecha de pago debe ser una fecha válida')]
        public string $fechaPago = '',

        #[Assert\Choice(
            choices: ['efectivo', 'transferencia', 'tarjeta', 'bizum'],
            message: 'El método de pago debe ser uno de: efectivo, transferencia, tarjeta, bizum'
        )]
        public ?string $metodoPago = null,

        #[Assert\NotBlank(message: 'La categoría es obligatoria')]
        #[Assert\Choice(
            choices: ['mensualidad', 'fianza', 'penalizacion', 'otros'],
            message: 'La categoría debe ser una de: mensualidad, fianza, penalizacion, otros'
        )]
        public string $categoria = 'mensualidad'
    ) {
    }
}
