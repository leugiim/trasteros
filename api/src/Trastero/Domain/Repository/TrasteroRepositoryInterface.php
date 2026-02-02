<?php

declare(strict_types=1);

namespace App\Trastero\Domain\Repository;

use App\Trastero\Domain\Model\Trastero;
use App\Trastero\Domain\Model\TrasteroEstado;
use App\Trastero\Domain\Model\TrasteroId;

interface TrasteroRepositoryInterface
{
    public function save(Trastero $trastero): void;

    public function remove(Trastero $trastero): void;

    public function findById(TrasteroId $id): ?Trastero;

    public function findByNumeroAndLocal(string $numero, int $localId): ?Trastero;

    /**
     * @return Trastero[]
     */
    public function findAll(): array;

    /**
     * @return Trastero[]
     */
    public function findActiveTrasteros(): array;

    /**
     * @return Trastero[]
     */
    public function findByLocalId(int $localId): array;

    /**
     * @return Trastero[]
     */
    public function findByEstado(TrasteroEstado $estado): array;

    /**
     * @return Trastero[]
     */
    public function findByLocalAndEstado(int $localId, TrasteroEstado $estado): array;

    /**
     * @return Trastero[]
     */
    public function findDisponiblesByLocal(int $localId): array;

    /**
     * @return Trastero[]
     */
    public function findOcupadosByLocal(int $localId): array;

    public function countByLocal(int $localId): int;

    public function countByLocalAndEstado(int $localId, TrasteroEstado $estado): int;

    public function getTotalSuperficieByLocal(int $localId): float;

    public function getTotalIngresosMensualesByLocal(int $localId): float;

    public function getTotalIngresosMensualesOcupadosByLocal(int $localId): float;

    public function existsByNumeroAndLocal(string $numero, int $localId): bool;

    /**
     * @return Trastero[]
     */
    public function findDisponibles(): array;

    public function countDisponibles(): int;

    public function countOcupados(): int;

    public function count(array $criteria = []): int;
}
