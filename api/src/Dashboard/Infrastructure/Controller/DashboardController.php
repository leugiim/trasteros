<?php

declare(strict_types=1);

namespace App\Dashboard\Infrastructure\Controller;

use App\Auth\Infrastructure\Attribute\Auth;
use App\Dashboard\Application\DTO\DashboardStatsResponse;
use App\Dashboard\Application\DTO\RentabilidadResponse;
use App\Dashboard\Application\Query\GetDashboardStats\GetDashboardStatsQuery;
use App\Dashboard\Application\Query\GetRentabilidad\GetRentabilidadQuery;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/dashboard')]
#[Auth]
#[OA\Tag(name: 'Dashboard')]
final class DashboardController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $queryBus
    ) {
    }

    #[Route('/stats', name: 'dashboard_stats', methods: ['GET'])]
    #[OA\Get(
        summary: 'Estadísticas del dashboard',
        description: 'Obtiene las estadísticas generales del negocio: ocupación, contratos, finanzas del mes'
    )]
    #[OA\Response(
        response: 200,
        description: 'Estadísticas del dashboard',
        content: new OA\JsonContent(ref: '#/components/schemas/DashboardStats')
    )]
    public function stats(): JsonResponse
    {
        $envelope = $this->queryBus->dispatch(new GetDashboardStatsQuery());
        $handledStamp = $envelope->last(HandledStamp::class);

        /** @var DashboardStatsResponse $stats */
        $stats = $handledStamp->getResult();

        return $this->json($stats->toArray());
    }

    #[Route('/rentabilidad', name: 'dashboard_rentabilidad', methods: ['GET'])]
    #[OA\Get(
        summary: 'Rentabilidad por local',
        description: 'Obtiene el desglose de rentabilidad por cada local: ocupación, ingresos, gastos y balance'
    )]
    #[OA\Response(
        response: 200,
        description: 'Rentabilidad por local',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'locales',
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'localId', type: 'integer'),
                            new OA\Property(property: 'nombre', type: 'string'),
                            new OA\Property(
                                property: 'trasteros',
                                properties: [
                                    new OA\Property(property: 'total', type: 'integer'),
                                    new OA\Property(property: 'ocupados', type: 'integer'),
                                    new OA\Property(property: 'tasaOcupacion', type: 'number')
                                ],
                                type: 'object'
                            ),
                            new OA\Property(
                                property: 'financiero',
                                properties: [
                                    new OA\Property(property: 'ingresosTotales', type: 'number'),
                                    new OA\Property(property: 'gastosTotales', type: 'number'),
                                    new OA\Property(property: 'balance', type: 'number'),
                                    new OA\Property(property: 'ingresosMensualesPotenciales', type: 'number'),
                                    new OA\Property(property: 'ingresosMensualesActuales', type: 'number')
                                ],
                                type: 'object'
                            )
                        ]
                    )
                )
            ]
        )
    )]
    public function rentabilidad(): JsonResponse
    {
        $envelope = $this->queryBus->dispatch(new GetRentabilidadQuery());
        $handledStamp = $envelope->last(HandledStamp::class);

        /** @var RentabilidadResponse $rentabilidad */
        $rentabilidad = $handledStamp->getResult();

        return $this->json($rentabilidad->toArray());
    }
}
