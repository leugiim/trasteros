<?php

declare(strict_types=1);

namespace App\Cliente\Domain\Repository;

use App\Cliente\Domain\Model\Cliente;
use App\Cliente\Domain\Model\ClienteId;

interface ClienteRepositoryInterface
{
    public function save(Cliente $cliente): void;

    public function remove(Cliente $cliente): void;

    public function findById(ClienteId $id): ?Cliente;

    public function findByDniNie(string $dniNie): ?Cliente;

    public function findByEmail(string $email): ?Cliente;

    /**
     * @return Cliente[]
     */
    public function findAll(): array;

    /**
     * @return Cliente[]
     */
    public function findActivos(): array;

    /**
     * @return Cliente[]
     */
    public function searchByNombreOrApellidos(string $searchTerm): array;

    public function existsByDniNie(string $dniNie, ?int $excludeId = null): bool;

    public function existsByEmail(string $email, ?int $excludeId = null): bool;

    public function count(): int;
}
