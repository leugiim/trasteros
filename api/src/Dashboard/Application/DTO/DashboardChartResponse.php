<?php

declare(strict_types=1);

namespace App\Dashboard\Application\DTO;

final readonly class DashboardChartResponse
{
    /**
     * @param array<array{date: string, ingresos: float, gastos: float}> $data
     */
    public function __construct(
        public string $period,
        public array $data
    ) {
    }

    public function toArray(): array
    {
        return [
            'period' => $this->period,
            'data' => $this->data,
        ];
    }
}
