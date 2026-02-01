<?php

declare(strict_types=1);

namespace App\Gasto\Application\DTO;

use App\Gasto\Domain\Model\Gasto;

final readonly class GastoResponse
{
    public function __construct(
        public int $id,
        public int $localId,
        public string $localNombre,
        public string $concepto,
        public ?string $descripcion,
        public float $importe,
        public string $fecha,
        public string $categoria,
        public ?string $metodoPago,
        public string $createdAt,
        public string $updatedAt,
        public ?string $deletedAt
    ) {
    }

    public static function fromGasto(Gasto $gasto): self
    {
        return new self(
            id: $gasto->id()->value,
            localId: $gasto->local()->id()->value,
            localNombre: $gasto->local()->nombre(),
            concepto: $gasto->concepto(),
            descripcion: $gasto->descripcion(),
            importe: $gasto->importe()->value,
            fecha: $gasto->fecha()->format('Y-m-d'),
            categoria: $gasto->categoria()->value,
            metodoPago: $gasto->metodoPago()?->value,
            createdAt: $gasto->createdAt()->format('Y-m-d H:i:s'),
            updatedAt: $gasto->updatedAt()->format('Y-m-d H:i:s'),
            deletedAt: $gasto->deletedAt()?->format('Y-m-d H:i:s')
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'localId' => $this->localId,
            'localNombre' => $this->localNombre,
            'concepto' => $this->concepto,
            'descripcion' => $this->descripcion,
            'importe' => $this->importe,
            'fecha' => $this->fecha,
            'categoria' => $this->categoria,
            'metodoPago' => $this->metodoPago,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'deletedAt' => $this->deletedAt,
        ];
    }
}
