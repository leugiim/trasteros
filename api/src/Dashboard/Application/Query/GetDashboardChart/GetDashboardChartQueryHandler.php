<?php

declare(strict_types=1);

namespace App\Dashboard\Application\Query\GetDashboardChart;

use App\Dashboard\Application\DTO\DashboardChartResponse;
use App\Gasto\Domain\Repository\GastoRepositoryInterface;
use App\Ingreso\Domain\Repository\IngresoRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetDashboardChartQueryHandler
{
    public function __construct(
        private IngresoRepositoryInterface $ingresoRepository,
        private GastoRepositoryInterface $gastoRepository
    ) {
    }

    public function __invoke(GetDashboardChartQuery $query): DashboardChartResponse
    {
        // Calculate date range based on period
        [$desde, $hasta, $groupBy] = $this->calculateDateRange($query->period);

        // Fetch data from repositories
        if ($groupBy === 'day') {
            $ingresosData = $this->ingresoRepository->getImportesGroupedByDay($desde, $hasta);
            $gastosData = $this->gastoRepository->getImportesGroupedByDay($desde, $hasta);
        } else {
            $ingresosData = $this->ingresoRepository->getImportesGroupedByMonth($desde, $hasta);
            $gastosData = $this->gastoRepository->getImportesGroupedByMonth($desde, $hasta);
        }

        // Convert to associative arrays for easier lookup
        $ingresosMap = $this->arrayToMap($ingresosData);
        $gastosMap = $this->arrayToMap($gastosData);

        // Generate all dates/months in the range and fill in missing data
        $chartData = $this->fillMissingDates($desde, $hasta, $groupBy, $ingresosMap, $gastosMap);

        return new DashboardChartResponse(
            period: $query->period,
            data: $chartData
        );
    }

    /**
     * @return array{0: \DateTimeImmutable, 1: \DateTimeImmutable, 2: string}
     */
    private function calculateDateRange(string $period): array
    {
        $hasta = new \DateTimeImmutable('today 23:59:59');

        return match ($period) {
            '1m' => [
                new \DateTimeImmutable('-1 month midnight'),
                $hasta,
                'day'
            ],
            '3m' => [
                new \DateTimeImmutable('-3 months midnight'),
                $hasta,
                'day'
            ],
            '6m' => [
                new \DateTimeImmutable('-6 months midnight'),
                $hasta,
                'month'
            ],
            '1y' => [
                new \DateTimeImmutable('-1 year midnight'),
                $hasta,
                'month'
            ],
            default => [
                new \DateTimeImmutable('-1 month midnight'),
                $hasta,
                'day'
            ],
        };
    }

    /**
     * @param array<array{date: string, total: float}> $data
     * @return array<string, float>
     */
    private function arrayToMap(array $data): array
    {
        $map = [];
        foreach ($data as $item) {
            $map[$item['date']] = $item['total'];
        }
        return $map;
    }

    /**
     * @param array<string, float> $ingresosMap
     * @param array<string, float> $gastosMap
     * @return array<array{date: string, ingresos: float, gastos: float}>
     */
    private function fillMissingDates(
        \DateTimeImmutable $desde,
        \DateTimeImmutable $hasta,
        string $groupBy,
        array $ingresosMap,
        array $gastosMap
    ): array {
        $result = [];
        $current = $desde;

        if ($groupBy === 'day') {
            while ($current <= $hasta) {
                $dateKey = $current->format('Y-m-d');
                $result[] = [
                    'date' => $dateKey,
                    'ingresos' => $ingresosMap[$dateKey] ?? 0.0,
                    'gastos' => $gastosMap[$dateKey] ?? 0.0,
                ];
                $current = $current->modify('+1 day');
            }
        } else {
            // Group by month
            while ($current <= $hasta) {
                $dateKey = $current->format('Y-m');
                $result[] = [
                    'date' => $dateKey,
                    'ingresos' => $ingresosMap[$dateKey] ?? 0.0,
                    'gastos' => $gastosMap[$dateKey] ?? 0.0,
                ];
                $current = $current->modify('+1 month');
            }
        }

        return $result;
    }
}
