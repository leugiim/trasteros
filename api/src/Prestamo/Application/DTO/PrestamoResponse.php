<?php

declare(strict_types=1);

namespace App\Prestamo\Application\DTO;

use App\Prestamo\Domain\Model\Prestamo;

final readonly class PrestamoResponse
{
    public function __construct(
        public int $id,
        public int $localId,
        public string $localNombre,
        public ?string $entidadBancaria,
        public ?string $numeroPrestamo,
        public float $capitalSolicitado,
        public float $totalADevolver,
        public ?float $tipoInteres,
        public string $fechaConcesion,
        public string $estado,
        public float $amortizado,
        public string $createdAt,
        public string $updatedAt,
        public ?string $deletedAt
    ) {
    }

    public static function fromPrestamo(Prestamo $prestamo, float $amortizado = 0.0): self
    {
        return new self(
            id: $prestamo->id()->value,
            localId: $prestamo->local()->id()->value,
            localNombre: $prestamo->local()->nombre(),
            entidadBancaria: $prestamo->entidadBancaria(),
            numeroPrestamo: $prestamo->numeroPrestamo(),
            capitalSolicitado: $prestamo->capitalSolicitado()->value,
            totalADevolver: $prestamo->totalADevolver()->value,
            tipoInteres: $prestamo->tipoInteres()?->value,
            fechaConcesion: $prestamo->fechaConcesion()->format('Y-m-d'),
            estado: $prestamo->estado()->value,
            amortizado: $amortizado,
            createdAt: $prestamo->createdAt()->format('Y-m-d H:i:s'),
            updatedAt: $prestamo->updatedAt()->format('Y-m-d H:i:s'),
            deletedAt: $prestamo->deletedAt()?->format('Y-m-d H:i:s')
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'localId' => $this->localId,
            'localNombre' => $this->localNombre,
            'entidadBancaria' => $this->entidadBancaria,
            'numeroPrestamo' => $this->numeroPrestamo,
            'capitalSolicitado' => $this->capitalSolicitado,
            'totalADevolver' => $this->totalADevolver,
            'tipoInteres' => $this->tipoInteres,
            'fechaConcesion' => $this->fechaConcesion,
            'estado' => $this->estado,
            'amortizado' => $this->amortizado,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'deletedAt' => $this->deletedAt,
        ];
    }
}
