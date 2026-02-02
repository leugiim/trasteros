<?php

declare(strict_types=1);

namespace App\Ingreso\Application\Query\FindIngresosByTrastero;

use App\Ingreso\Application\DTO\IngresoResponse;
use App\Ingreso\Domain\Repository\IngresoRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class FindIngresosByTrasteroQueryHandler
{
    public function __construct(
        private IngresoRepositoryInterface $ingresoRepository
    ) {
    }

    /**
     * @return IngresoResponse[]
     */
    public function __invoke(FindIngresosByTrasteroQuery $query): array
    {
        $ingresos = $this->ingresoRepository->findByTrasteroId($query->trasteroId);

        return array_map(
            fn($ingreso) => IngresoResponse::fromIngreso($ingreso),
            $ingresos
        );
    }
}
