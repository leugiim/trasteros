<?php

declare(strict_types=1);

namespace App\Dashboard\Application\DTO;

final readonly class DashboardStatsResponse
{
    public function __construct(
        public int $totalTrasteros,
        public int $trasterosDisponibles,
        public int $trasterosOcupados,
        public float $tasaOcupacion,
        public int $contratosActivos,
        public int $totalContratos,
        public int $totalClientes,
        public int $totalLocales,
        public float $ingresosMes,
        public float $gastosMes,
        public float $balanceMes,
        public int $contratosProximosVencer,
        public int $fianzasPendientes
    ) {
    }

    public function toArray(): array
    {
        return [
            'trasteros' => [
                'total' => $this->totalTrasteros,
                'disponibles' => $this->trasterosDisponibles,
                'ocupados' => $this->trasterosOcupados,
                'tasaOcupacion' => $this->tasaOcupacion,
            ],
            'contratos' => [
                'activos' => $this->contratosActivos,
                'total' => $this->totalContratos,
                'proximosAVencer' => $this->contratosProximosVencer,
                'fianzasPendientes' => $this->fianzasPendientes,
            ],
            'entidades' => [
                'clientes' => $this->totalClientes,
                'locales' => $this->totalLocales,
            ],
            'financiero' => [
                'ingresosMes' => $this->ingresosMes,
                'gastosMes' => $this->gastosMes,
                'balanceMes' => $this->balanceMes,
            ],
        ];
    }
}
