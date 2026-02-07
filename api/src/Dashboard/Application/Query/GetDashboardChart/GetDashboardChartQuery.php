<?php

declare(strict_types=1);

namespace App\Dashboard\Application\Query\GetDashboardChart;

final readonly class GetDashboardChartQuery
{
    public function __construct(
        public string $period
    ) {
    }
}
