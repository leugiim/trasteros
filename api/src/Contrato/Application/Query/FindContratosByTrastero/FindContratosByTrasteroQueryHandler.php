<?php

declare(strict_types=1);

namespace App\Contrato\Application\Query\FindContratosByTrastero;

use App\Contrato\Application\DTO\ContratoResponse;
use App\Contrato\Domain\Repository\ContratoRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class FindContratosByTrasteroQueryHandler
{
    public function __construct(
        private ContratoRepositoryInterface $contratoRepository
    ) {
    }

    public function __invoke(FindContratosByTrasteroQuery $query): array
    {
        if ($query->onlyActivos) {
            $contratos = $this->contratoRepository->findContratosActivosByTrastero($query->trasteroId);
        } else {
            $contratos = $this->contratoRepository->findByTrasteroId($query->trasteroId);
        }

        return array_map(
            fn($contrato) => ContratoResponse::fromContrato($contrato),
            $contratos
        );
    }
}
