<?php

declare(strict_types=1);

namespace App\Dashboard\Application\Query\GetDashboardStats;

use App\Cliente\Domain\Repository\ClienteRepositoryInterface;
use App\Contrato\Domain\Repository\ContratoRepositoryInterface;
use App\Dashboard\Application\DTO\DashboardStatsResponse;
use App\Gasto\Domain\Repository\GastoRepositoryInterface;
use App\Ingreso\Domain\Repository\IngresoRepositoryInterface;
use App\Local\Domain\Repository\LocalRepositoryInterface;
use App\Prestamo\Domain\Model\PrestamoEstado;
use App\Prestamo\Domain\Repository\PrestamoRepositoryInterface;
use App\Trastero\Domain\Repository\TrasteroRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetDashboardStatsQueryHandler
{
    public function __construct(
        private TrasteroRepositoryInterface $trasteroRepository,
        private ContratoRepositoryInterface $contratoRepository,
        private ClienteRepositoryInterface $clienteRepository,
        private LocalRepositoryInterface $localRepository,
        private IngresoRepositoryInterface $ingresoRepository,
        private GastoRepositoryInterface $gastoRepository,
        private PrestamoRepositoryInterface $prestamoRepository
    ) {
    }

    public function __invoke(GetDashboardStatsQuery $query): DashboardStatsResponse
    {
        $totalTrasteros = $this->trasteroRepository->count();
        $trasterosOcupados = $this->contratoRepository->countTrasterosOcupados();
        $trasterosDisponibles = $totalTrasteros - $trasterosOcupados - $this->contratoRepository->countTrasterosReservados();

        $tasaOcupacion = $totalTrasteros > 0
            ? round(($trasterosOcupados / $totalTrasteros) * 100, 2)
            : 0.0;

        $contratosActivos = $this->contratoRepository->countContratosActivos();
        $totalContratos = $this->contratoRepository->count();

        $totalClientes = $this->clienteRepository->count();
        $totalLocales = $this->localRepository->count();

        // Ingresos y gastos del mes actual
        $inicioMes = new \DateTimeImmutable('first day of this month midnight');
        $finMes = new \DateTimeImmutable('last day of this month 23:59:59');

        $ingresosMes = $this->ingresoRepository->getTotalImporteByDateRange($inicioMes, $finMes);
        $gastosMes = $this->gastoRepository->getTotalImporteByDateRange($inicioMes, $finMes);

        // Contratos próximos a vencer (30 días)
        $contratosProximosVencer = count($this->contratoRepository->findProximosAVencer(30));

        // Fianzas pendientes
        $fianzasPendientes = count($this->contratoRepository->findConFianzaPendiente());

        // Préstamos pendientes
        $prestamosPendienteTotal = $this->prestamoRepository->getTotalADevolverByEstado(PrestamoEstado::ACTIVO->value);

        return new DashboardStatsResponse(
            totalTrasteros: $totalTrasteros,
            trasterosDisponibles: max(0, $trasterosDisponibles),
            trasterosOcupados: $trasterosOcupados,
            tasaOcupacion: $tasaOcupacion,
            contratosActivos: $contratosActivos,
            totalContratos: $totalContratos,
            totalClientes: $totalClientes,
            totalLocales: $totalLocales,
            ingresosMes: $ingresosMes,
            gastosMes: $gastosMes,
            balanceMes: $ingresosMes - $gastosMes,
            contratosProximosVencer: $contratosProximosVencer,
            fianzasPendientes: $fianzasPendientes,
            prestamosPendienteTotal: $prestamosPendienteTotal
        );
    }
}
