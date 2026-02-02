<?php

declare(strict_types=1);

namespace App\Dashboard\Infrastructure\Controller;

use App\Auth\Infrastructure\Attribute\Auth;
use App\Dashboard\Application\DTO\DashboardStatsResponse;
use App\Dashboard\Application\DTO\RentabilidadResponse;
use App\Dashboard\Application\Query\GetDashboardStats\GetDashboardStatsQuery;
use App\Dashboard\Application\Query\GetRentabilidad\GetRentabilidadQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/dashboard')]
#[Auth]
final class DashboardController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $queryBus
    ) {
    }

    #[Route('/stats', name: 'dashboard_stats', methods: ['GET'])]
    public function stats(): JsonResponse
    {
        $envelope = $this->queryBus->dispatch(new GetDashboardStatsQuery());
        $handledStamp = $envelope->last(HandledStamp::class);

        /** @var DashboardStatsResponse $stats */
        $stats = $handledStamp->getResult();

        return $this->json($stats->toArray());
    }

    #[Route('/rentabilidad', name: 'dashboard_rentabilidad', methods: ['GET'])]
    public function rentabilidad(): JsonResponse
    {
        $envelope = $this->queryBus->dispatch(new GetRentabilidadQuery());
        $handledStamp = $envelope->last(HandledStamp::class);

        /** @var RentabilidadResponse $rentabilidad */
        $rentabilidad = $handledStamp->getResult();

        return $this->json($rentabilidad->toArray());
    }
}
