<?php

declare(strict_types=1);

namespace App\Ingreso\Application\DTO;

use App\Ingreso\Domain\Model\Ingreso;

final readonly class IngresoResponse
{
    public function __construct(
        public int $id,
        public int $contratoId,
        public string $contratoInfo,
        public string $concepto,
        public float $importe,
        public string $fechaPago,
        public ?string $metodoPago,
        public string $categoria,
        public string $createdAt,
        public string $updatedAt,
        public ?string $deletedAt
    ) {
    }

    public static function fromIngreso(Ingreso $ingreso): self
    {
        $contrato = $ingreso->contrato();
        $contratoInfo = sprintf(
            'Trastero: %s - Cliente: %s %s',
            $contrato->trastero()->numero(),
            $contrato->cliente()->nombre(),
            $contrato->cliente()->apellidos()
        );

        return new self(
            id: $ingreso->id()->value,
            contratoId: $contrato->id()->value,
            contratoInfo: $contratoInfo,
            concepto: $ingreso->concepto(),
            importe: $ingreso->importe()->value,
            fechaPago: $ingreso->fechaPago()->format('Y-m-d'),
            metodoPago: $ingreso->metodoPago()?->value,
            categoria: $ingreso->categoria()->value,
            createdAt: $ingreso->createdAt()->format('Y-m-d H:i:s'),
            updatedAt: $ingreso->updatedAt()->format('Y-m-d H:i:s'),
            deletedAt: $ingreso->deletedAt()?->format('Y-m-d H:i:s')
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'contratoId' => $this->contratoId,
            'contratoInfo' => $this->contratoInfo,
            'concepto' => $this->concepto,
            'importe' => $this->importe,
            'fechaPago' => $this->fechaPago,
            'metodoPago' => $this->metodoPago,
            'categoria' => $this->categoria,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'deletedAt' => $this->deletedAt,
        ];
    }
}
