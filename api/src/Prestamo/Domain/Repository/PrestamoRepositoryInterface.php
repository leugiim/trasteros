<?php

declare(strict_types=1);

namespace App\Prestamo\Domain\Repository;

use App\Prestamo\Domain\Model\Prestamo;
use App\Prestamo\Domain\Model\PrestamoId;

interface PrestamoRepositoryInterface
{
    public function save(Prestamo $prestamo): void;

    public function remove(Prestamo $prestamo): void;

    public function findById(PrestamoId $id): ?Prestamo;

    /**
     * @return Prestamo[]
     */
    public function findAll(): array;

    /**
     * @return Prestamo[]
     */
    public function findActivePrestamos(): array;

    /**
     * @return Prestamo[]
     */
    public function findByLocalId(int $localId): array;

    /**
     * @return Prestamo[]
     */
    public function findByEstado(string $estado): array;

    /**
     * @return Prestamo[]
     */
    public function findByEntidadBancaria(string $entidadBancaria): array;

    public function count(): int;

    public function getTotalADevolverByEstado(string $estado): float;
}
