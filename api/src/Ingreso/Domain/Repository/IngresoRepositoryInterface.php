<?php

declare(strict_types=1);

namespace App\Ingreso\Domain\Repository;

use App\Ingreso\Domain\Model\Ingreso;
use App\Ingreso\Domain\Model\IngresoCategoria;
use App\Ingreso\Domain\Model\IngresoId;

interface IngresoRepositoryInterface
{
    public function save(Ingreso $ingreso): void;

    public function remove(Ingreso $ingreso): void;

    public function findById(IngresoId $id): ?Ingreso;

    /**
     * @return Ingreso[]
     */
    public function findAll(): array;

    /**
     * @return Ingreso[]
     */
    public function findActiveIngresos(): array;

    /**
     * @return Ingreso[]
     */
    public function findByContratoId(int $contratoId): array;

    /**
     * @return Ingreso[]
     */
    public function findByCategoria(IngresoCategoria $categoria): array;

    /**
     * @return Ingreso[]
     */
    public function findByDateRange(\DateTimeImmutable $desde, \DateTimeImmutable $hasta): array;

    /**
     * @return Ingreso[]
     */
    public function findByContratoAndDateRange(
        int $contratoId,
        \DateTimeImmutable $desde,
        \DateTimeImmutable $hasta
    ): array;

    /**
     * @return Ingreso[]
     */
    public function findByContratoAndCategoria(int $contratoId, IngresoCategoria $categoria): array;

    public function getTotalImporteByContrato(int $contratoId): float;

    public function getTotalImporteByContratoAndCategoria(int $contratoId, IngresoCategoria $categoria): float;

    public function getTotalImporteByContratoAndDateRange(
        int $contratoId,
        \DateTimeImmutable $desde,
        \DateTimeImmutable $hasta
    ): float;

    public function count(array $criteria = []): int;

    /**
     * @return Ingreso[]
     */
    public function findByTrasteroId(int $trasteroId): array;

    /**
     * @return Ingreso[]
     */
    public function findByLocalId(int $localId): array;

    public function getTotalImporteByTrastero(int $trasteroId): float;

    public function getTotalImporteByLocal(int $localId): float;

    public function getTotalImporteByDateRange(\DateTimeImmutable $desde, \DateTimeImmutable $hasta): float;

    /**
     * Get income amounts grouped by day
     * @return array<array{date: string, total: float}> Array of ['date' => 'YYYY-MM-DD', 'total' => float]
     */
    public function getImportesGroupedByDay(\DateTimeImmutable $desde, \DateTimeImmutable $hasta): array;

    /**
     * Get income amounts grouped by month
     * @return array<array{date: string, total: float}> Array of ['date' => 'YYYY-MM', 'total' => float]
     */
    public function getImportesGroupedByMonth(\DateTimeImmutable $desde, \DateTimeImmutable $hasta): array;
}
