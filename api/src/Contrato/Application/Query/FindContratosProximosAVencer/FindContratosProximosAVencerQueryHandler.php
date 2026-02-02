<?php

declare(strict_types=1);

namespace App\Contrato\Application\Query\FindContratosProximosAVencer;

use App\Contrato\Application\DTO\ContratoResponse;
use App\Contrato\Domain\Repository\ContratoRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class FindContratosProximosAVencerQueryHandler
{
    public function __construct(
        private ContratoRepositoryInterface $contratoRepository
    ) {
    }

    /**
     * @return ContratoResponse[]
     */
    public function __invoke(FindContratosProximosAVencerQuery $query): array
    {
        $contratos = $this->contratoRepository->findProximosAVencer($query->dias);

        return array_map(
            fn($contrato) => ContratoResponse::fromContrato($contrato),
            $contratos
        );
    }
}
