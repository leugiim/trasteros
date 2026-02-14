<?php

declare(strict_types=1);

namespace App\Gasto\Application\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class GastoRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'El local es obligatorio')]
        #[Assert\Positive(message: 'El ID de local debe ser un número positivo')]
        public int $localId = 0,

        #[Assert\NotBlank(message: 'El concepto es obligatorio')]
        #[Assert\Length(max: 255, maxMessage: 'El concepto no puede superar los 255 caracteres')]
        public string $concepto = '',

        public ?string $descripcion = null,

        #[Assert\NotBlank(message: 'El importe es obligatorio')]
        #[Assert\Positive(message: 'El importe debe ser un número positivo')]
        public float $importe = 0.0,

        #[Assert\NotBlank(message: 'La fecha es obligatoria')]
        #[Assert\Date(message: 'La fecha debe ser una fecha válida')]
        public string $fecha = '',

        #[Assert\NotBlank(message: 'La categoría es obligatoria')]
        #[Assert\Choice(
            choices: ['suministros', 'seguros', 'impuestos', 'mantenimiento', 'prestamo', 'gestoria', 'otros'],
            message: 'La categoría debe ser una de: suministros, seguros, impuestos, mantenimiento, prestamo, gestoria, otros'
        )]
        public string $categoria = '',

        #[Assert\Choice(
            choices: ['efectivo', 'transferencia', 'tarjeta', 'domiciliacion'],
            message: 'El método de pago debe ser uno de: efectivo, transferencia, tarjeta, domiciliacion'
        )]
        public ?string $metodoPago = null,

        #[Assert\Positive(message: 'El ID de préstamo debe ser un número positivo')]
        public ?int $prestamoId = null
    ) {
    }
}
