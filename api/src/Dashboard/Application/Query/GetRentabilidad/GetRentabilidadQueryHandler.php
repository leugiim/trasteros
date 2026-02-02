<?php

declare(strict_types=1);

namespace App\Dashboard\Application\Query\GetRentabilidad;

use App\Dashboard\Application\DTO\RentabilidadResponse;
use App\Gasto\Domain\Repository\GastoRepositoryInterface;
use App\Ingreso\Domain\Repository\IngresoRepositoryInterface;
use App\Local\Domain\Repository\LocalRepositoryInterface;
use App\Trastero\Domain\Repository\TrasteroRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetRentabilidadQueryHandler
{
    public function __construct(
        private LocalRepositoryInterface $localRepository,
        private TrasteroRepositoryInterface $trasteroRepository,
        private IngresoRepositoryInterface $ingresoRepository,
        private GastoRepositoryInterface $gastoRepository
    ) {
    }

    public function __invoke(GetRentabilidadQuery $query): RentabilidadResponse
    {
        $locales = $this->localRepository->findAll();
        $rentabilidadPorLocal = [];

        foreach ($locales as $local) {
            $localId = $local->id()->value;

            $totalTrasteros = $this->trasteroRepository->countByLocal($localId);
            $trasterosOcupados = $this->trasteroRepository->countByLocalAndEstado(
                $localId,
                \App\Trastero\Domain\Model\TrasteroEstado::OCUPADO
            );
            $tasaOcupacion = $totalTrasteros > 0
                ? round(($trasterosOcupados / $totalTrasteros) * 100, 2)
                : 0.0;

            $ingresosTotales = $this->ingresoRepository->getTotalImporteByLocal($localId);
            $gastosTotales = $this->gastoRepository->getTotalImporteByLocal($localId);
            $balance = $ingresosTotales - $gastosTotales;

            $ingresosMensualesPotenciales = $this->trasteroRepository->getTotalIngresosMensualesByLocal($localId);
            $ingresosMensualesActuales = $this->trasteroRepository->getTotalIngresosMensualesOcupadosByLocal($localId);

            $rentabilidadPorLocal[] = [
                'localId' => $localId,
                'nombre' => $local->nombre(),
                'trasteros' => [
                    'total' => $totalTrasteros,
                    'ocupados' => $trasterosOcupados,
                    'tasaOcupacion' => $tasaOcupacion,
                ],
                'financiero' => [
                    'ingresosTotales' => $ingresosTotales,
                    'gastosTotales' => $gastosTotales,
                    'balance' => $balance,
                    'ingresosMensualesPotenciales' => $ingresosMensualesPotenciales,
                    'ingresosMensualesActuales' => $ingresosMensualesActuales,
                ],
            ];
        }

        return new RentabilidadResponse($rentabilidadPorLocal);
    }
}
