<?php

declare(strict_types=1);

namespace App\Ingreso\Application\Query\FindIngresosByContrato;

use App\Ingreso\Application\DTO\IngresoResponse;
use App\Ingreso\Domain\Repository\IngresoRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class FindIngresosByContratoQueryHandler
{
    public function __construct(
        private IngresoRepositoryInterface $ingresoRepository
    ) {
    }

    /**
     * @return IngresoResponse[]
     */
    public function __invoke(FindIngresosByContratoQuery $query): array
    {
        $ingresos = $this->ingresoRepository->findByContratoId($query->contratoId);

        return array_map(
            fn($ingreso) => IngresoResponse::fromIngreso($ingreso),
            $ingresos
        );
    }
}
