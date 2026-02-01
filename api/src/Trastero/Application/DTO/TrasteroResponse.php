<?php

declare(strict_types=1);

namespace App\Trastero\Application\DTO;

use App\Trastero\Domain\Model\Trastero;

final readonly class TrasteroResponse
{
    public function __construct(
        public int $id,
        public int $localId,
        public string $localNombre,
        public string $numero,
        public ?string $nombre,
        public float $superficie,
        public float $precioMensual,
        public string $estado,
        public string $estadoLabel,
        public string $createdAt,
        public string $updatedAt,
        public ?string $deletedAt
    ) {
    }

    public static function fromTrastero(Trastero $trastero): self
    {
        return new self(
            id: $trastero->id()->value,
            localId: $trastero->local()->id()->value,
            localNombre: $trastero->local()->nombre(),
            numero: $trastero->numero(),
            nombre: $trastero->nombre(),
            superficie: $trastero->superficie()->value,
            precioMensual: $trastero->precioMensual()->value,
            estado: $trastero->estado()->value,
            estadoLabel: $trastero->estado()->label(),
            createdAt: $trastero->createdAt()->format('Y-m-d H:i:s'),
            updatedAt: $trastero->updatedAt()->format('Y-m-d H:i:s'),
            deletedAt: $trastero->deletedAt()?->format('Y-m-d H:i:s')
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'localId' => $this->localId,
            'localNombre' => $this->localNombre,
            'numero' => $this->numero,
            'nombre' => $this->nombre,
            'superficie' => $this->superficie,
            'precioMensual' => $this->precioMensual,
            'estado' => $this->estado,
            'estadoLabel' => $this->estadoLabel,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'deletedAt' => $this->deletedAt,
        ];
    }
}
