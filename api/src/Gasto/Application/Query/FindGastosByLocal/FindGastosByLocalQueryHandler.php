<?php

declare(strict_types=1);

namespace App\Gasto\Application\Query\FindGastosByLocal;

use App\Gasto\Application\DTO\GastoResponse;
use App\Gasto\Domain\Repository\GastoRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class FindGastosByLocalQueryHandler
{
    public function __construct(
        private GastoRepositoryInterface $gastoRepository
    ) {
    }

    /**
     * @return GastoResponse[]
     */
    public function __invoke(FindGastosByLocalQuery $query): array
    {
        $gastos = $this->gastoRepository->findByLocalId($query->localId);

        return array_map(
            fn($gasto) => GastoResponse::fromGasto($gasto),
            $gastos
        );
    }
}
