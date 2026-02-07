<?php

declare(strict_types=1);

namespace App\Contrato\Domain\Repository;

use App\Contrato\Domain\Model\Contrato;

interface ContratoRepositoryInterface
{
    public function save(Contrato $contrato): void;

    public function findById(int $id): ?Contrato;

    public function findAll(): array;

    public function findByTrasteroId(int $trasteroId): array;

    public function findByClienteId(int $clienteId): array;

    /**
     * @return Contrato[] Contratos activos hoy (por fechas, no cancelados)
     */
    public function findContratosActivosByCliente(int $clienteId): array;

    /**
     * @return Contrato[] Contratos activos hoy para un trastero (por fechas, no cancelados)
     */
    public function findContratosActivosByTrastero(int $trasteroId): array;

    /**
     * @return Contrato[] Contratos que se solapan con el rango de fechas dado para un trastero
     */
    public function findContratosSolapados(
        int $trasteroId,
        \DateTimeImmutable $inicio,
        ?\DateTimeImmutable $fin = null,
        ?int $excludeContratoId = null
    ): array;

    /**
     * @return Contrato[] Contratos activos hoy con fechaFin dentro de los proximos N dias
     */
    public function findProximosAVencer(int $dias = 30): array;

    /**
     * @return Contrato[] Contratos activos hoy con fianza pendiente
     */
    public function findConFianzaPendiente(): array;

    public function remove(Contrato $contrato): void;

    public function countContratosActivos(): int;

    /**
     * Cuenta trasteros ocupados (con contrato activo hoy, no en mantenimiento)
     */
    public function countTrasterosOcupados(): int;

    /**
     * Cuenta trasteros ocupados en un local concreto (con contrato activo hoy)
     */
    public function countTrasterosOcupadosByLocal(int $localId): int;

    /**
     * Cuenta trasteros con contrato pendiente (futuro, no en mantenimiento)
     */
    public function countTrasterosReservados(): int;

    public function count(array $criteria = []): int;
}
