<?php

declare(strict_types=1);

namespace App\Cliente\Application\DTO;

use App\Cliente\Domain\Model\Cliente;

final readonly class ClienteResponse
{
    public function __construct(
        public int $id,
        public string $nombre,
        public string $apellidos,
        public string $nombreCompleto,
        public ?string $dniNie,
        public ?string $email,
        public ?string $telefono,
        public bool $activo,
        public string $createdAt,
        public string $updatedAt,
        public ?string $deletedAt
    ) {
    }

    public static function fromCliente(Cliente $cliente): self
    {
        $clienteId = $cliente->id();

        return new self(
            id: $clienteId?->value ?? 0,
            nombre: $cliente->nombre(),
            apellidos: $cliente->apellidos(),
            nombreCompleto: $cliente->nombreCompleto(),
            dniNie: $cliente->dniNie()?->value,
            email: $cliente->email()?->value,
            telefono: $cliente->telefono()?->value,
            activo: $cliente->activo(),
            createdAt: $cliente->createdAt()->format(\DateTimeInterface::ATOM),
            updatedAt: $cliente->updatedAt()->format(\DateTimeInterface::ATOM),
            deletedAt: $cliente->deletedAt()?->format(\DateTimeInterface::ATOM)
        );
    }

    /**
     * @param Cliente[] $clientes
     * @return self[]
     */
    public static function fromClientes(array $clientes): array
    {
        return array_map(fn(Cliente $cliente) => self::fromCliente($cliente), $clientes);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'apellidos' => $this->apellidos,
            'nombreCompleto' => $this->nombreCompleto,
            'dniNie' => $this->dniNie,
            'email' => $this->email,
            'telefono' => $this->telefono,
            'activo' => $this->activo,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'deletedAt' => $this->deletedAt,
        ];
    }
}
