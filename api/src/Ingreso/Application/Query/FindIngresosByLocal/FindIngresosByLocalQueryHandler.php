<?php

declare(strict_types=1);

namespace App\Ingreso\Application\Query\FindIngresosByLocal;

use App\Ingreso\Application\DTO\IngresoResponse;
use App\Ingreso\Domain\Repository\IngresoRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class FindIngresosByLocalQueryHandler
{
    public function __construct(
        private IngresoRepositoryInterface $ingresoRepository
    ) {
    }

    /**
     * @return IngresoResponse[]
     */
    public function __invoke(FindIngresosByLocalQuery $query): array
    {
        $ingresos = $this->ingresoRepository->findByLocalId($query->localId);

        return array_map(
            fn($ingreso) => IngresoResponse::fromIngreso($ingreso),
            $ingresos
        );
    }
}
