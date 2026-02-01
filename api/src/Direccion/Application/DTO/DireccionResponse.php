<?php

declare(strict_types=1);

namespace App\Direccion\Application\DTO;

use App\Direccion\Domain\Model\Direccion;

final readonly class DireccionResponse
{
    public function __construct(
        public int $id,
        public ?string $tipoVia,
        public string $nombreVia,
        public ?string $numero,
        public ?string $piso,
        public ?string $puerta,
        public string $codigoPostal,
        public string $ciudad,
        public string $provincia,
        public string $pais,
        public ?float $latitud,
        public ?float $longitud,
        public string $direccionCompleta,
        public string $createdAt,
        public string $updatedAt,
        public ?string $deletedAt
    ) {
    }

    public static function fromDireccion(Direccion $direccion): self
    {
        $coordenadas = $direccion->coordenadas();
        $direccionId = $direccion->id();

        return new self(
            id: $direccionId?->value ?? 0,
            tipoVia: $direccion->tipoVia(),
            nombreVia: $direccion->nombreVia(),
            numero: $direccion->numero(),
            piso: $direccion->piso(),
            puerta: $direccion->puerta(),
            codigoPostal: $direccion->codigoPostal()->value,
            ciudad: $direccion->ciudad(),
            provincia: $direccion->provincia(),
            pais: $direccion->pais(),
            latitud: $coordenadas->latitud,
            longitud: $coordenadas->longitud,
            direccionCompleta: $direccion->direccionCompleta(),
            createdAt: $direccion->createdAt()->format(\DateTimeInterface::ATOM),
            updatedAt: $direccion->updatedAt()->format(\DateTimeInterface::ATOM),
            deletedAt: $direccion->deletedAt()?->format(\DateTimeInterface::ATOM)
        );
    }

    /**
     * @param Direccion[] $direcciones
     * @return self[]
     */
    public static function fromDirecciones(array $direcciones): array
    {
        return array_map(fn(Direccion $direccion) => self::fromDireccion($direccion), $direcciones);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'tipoVia' => $this->tipoVia,
            'nombreVia' => $this->nombreVia,
            'numero' => $this->numero,
            'piso' => $this->piso,
            'puerta' => $this->puerta,
            'codigoPostal' => $this->codigoPostal,
            'ciudad' => $this->ciudad,
            'provincia' => $this->provincia,
            'pais' => $this->pais,
            'latitud' => $this->latitud,
            'longitud' => $this->longitud,
            'direccionCompleta' => $this->direccionCompleta,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'deletedAt' => $this->deletedAt,
        ];
    }
}
