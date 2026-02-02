<?php

declare(strict_types=1);

namespace App\Gasto\Domain\Repository;

use App\Gasto\Domain\Model\Gasto;
use App\Gasto\Domain\Model\GastoCategoria;
use App\Gasto\Domain\Model\GastoId;

interface GastoRepositoryInterface
{
    public function save(Gasto $gasto): void;

    public function remove(Gasto $gasto): void;

    public function findById(GastoId $id): ?Gasto;

    /**
     * @return Gasto[]
     */
    public function findAll(): array;

    /**
     * @return Gasto[]
     */
    public function findActiveGastos(): array;

    /**
     * @return Gasto[]
     */
    public function findByLocalId(int $localId): array;

    /**
     * @return Gasto[]
     */
    public function findByCategoria(GastoCategoria $categoria): array;

    /**
     * @return Gasto[]
     */
    public function findByDateRange(\DateTimeImmutable $desde, \DateTimeImmutable $hasta): array;

    /**
     * @return Gasto[]
     */
    public function findByLocalAndDateRange(
        int $localId,
        \DateTimeImmutable $desde,
        \DateTimeImmutable $hasta
    ): array;

    /**
     * @return Gasto[]
     */
    public function findByLocalAndCategoria(int $localId, GastoCategoria $categoria): array;

    public function getTotalImporteByLocal(int $localId): float;

    public function getTotalImporteByLocalAndCategoria(int $localId, GastoCategoria $categoria): float;

    public function getTotalImporteByLocalAndDateRange(
        int $localId,
        \DateTimeImmutable $desde,
        \DateTimeImmutable $hasta
    ): float;

    public function count(array $criteria = []): int;

    public function getTotalImporteByDateRange(\DateTimeImmutable $desde, \DateTimeImmutable $hasta): float;
}
