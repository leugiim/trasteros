<?php

declare(strict_types=1);

namespace App\Ingreso\Application\Query\FindIngreso;

use App\Ingreso\Application\DTO\IngresoResponse;
use App\Ingreso\Domain\Exception\IngresoNotFoundException;
use App\Ingreso\Domain\Model\IngresoId;
use App\Ingreso\Domain\Repository\IngresoRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class FindIngresoQueryHandler
{
    public function __construct(
        private IngresoRepositoryInterface $ingresoRepository
    ) {
    }

    public function __invoke(FindIngresoQuery $query): IngresoResponse
    {
        $ingreso = $this->ingresoRepository->findById(IngresoId::fromInt($query->id));

        if ($ingreso === null) {
            throw IngresoNotFoundException::withId($query->id);
        }

        return IngresoResponse::fromIngreso($ingreso);
    }
}
