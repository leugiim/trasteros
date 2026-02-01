<?php

declare(strict_types=1);

namespace App\Gasto\Application\Query\FindGasto;

use App\Gasto\Application\DTO\GastoResponse;
use App\Gasto\Domain\Exception\GastoNotFoundException;
use App\Gasto\Domain\Model\GastoId;
use App\Gasto\Domain\Repository\GastoRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class FindGastoQueryHandler
{
    public function __construct(
        private GastoRepositoryInterface $gastoRepository
    ) {
    }

    public function __invoke(FindGastoQuery $query): GastoResponse
    {
        $gasto = $this->gastoRepository->findById(GastoId::fromInt($query->id));

        if ($gasto === null) {
            throw GastoNotFoundException::withId($query->id);
        }

        return GastoResponse::fromGasto($gasto);
    }
}
