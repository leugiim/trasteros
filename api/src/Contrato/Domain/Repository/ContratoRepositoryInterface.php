<?php

declare(strict_types=1);

namespace App\Contrato\Domain\Repository;

use App\Contrato\Domain\Model\Contrato;
use App\Contrato\Domain\Model\ContratoEstado;

interface ContratoRepositoryInterface
{
    public function save(Contrato $contrato): void;

    public function findById(int $id): ?Contrato;

    public function findAll(): array;

    public function findByTrasteroId(int $trasteroId): array;

    public function findByClienteId(int $clienteId): array;

    public function findByEstado(ContratoEstado $estado): array;

    public function findContratosActivosByCliente(int $clienteId): array;

    public function findContratosActivosByTrastero(int $trasteroId): array;

    public function hasContratoActivoTrastero(int $trasteroId): bool;

    public function findOneContratoActivoByTrastero(int $trasteroId): ?Contrato;

    /**
     * @return Contrato[]
     */
    public function findProximosAVencer(int $dias = 30): array;

    /**
     * @return Contrato[]
     */
    public function findConFianzaPendiente(): array;

    public function remove(Contrato $contrato): void;

    public function countByEstado(ContratoEstado $estado): int;

    public function count(): int;
}
