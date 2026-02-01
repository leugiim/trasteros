<?php

declare(strict_types=1);

namespace App\Local\Application\DTO;

use App\Direccion\Application\DTO\DireccionResponse;
use App\Local\Domain\Model\Local;

final readonly class LocalResponse
{
    public function __construct(
        public int $id,
        public string $nombre,
        public DireccionResponse $direccion,
        public ?float $superficieTotal,
        public ?int $numeroTrasteros,
        public ?string $fechaCompra,
        public ?float $precioCompra,
        public ?string $referenciaCatastral,
        public ?float $valorCatastral,
        public string $createdAt,
        public string $updatedAt,
        public ?string $deletedAt
    ) {
    }

    public static function fromLocal(Local $local): self
    {
        $localId = $local->id();

        return new self(
            id: $localId?->value ?? 0,
            nombre: $local->nombre(),
            direccion: DireccionResponse::fromDireccion($local->direccion()),
            superficieTotal: $local->superficieTotal(),
            numeroTrasteros: $local->numeroTrasteros(),
            fechaCompra: $local->fechaCompra()?->format('Y-m-d'),
            precioCompra: $local->precioCompra(),
            referenciaCatastral: $local->referenciaCatastral()?->value,
            valorCatastral: $local->valorCatastral(),
            createdAt: $local->createdAt()->format(\DateTimeInterface::ATOM),
            updatedAt: $local->updatedAt()->format(\DateTimeInterface::ATOM),
            deletedAt: $local->deletedAt()?->format(\DateTimeInterface::ATOM)
        );
    }

    /**
     * @param Local[] $locales
     * @return self[]
     */
    public static function fromLocales(array $locales): array
    {
        return array_map(fn(Local $local) => self::fromLocal($local), $locales);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'direccion' => $this->direccion->toArray(),
            'superficieTotal' => $this->superficieTotal,
            'numeroTrasteros' => $this->numeroTrasteros,
            'fechaCompra' => $this->fechaCompra,
            'precioCompra' => $this->precioCompra,
            'referenciaCatastral' => $this->referenciaCatastral,
            'valorCatastral' => $this->valorCatastral,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'deletedAt' => $this->deletedAt,
        ];
    }
}
