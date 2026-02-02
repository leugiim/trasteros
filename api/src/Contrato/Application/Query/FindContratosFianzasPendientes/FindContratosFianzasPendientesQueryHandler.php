<?php

declare(strict_types=1);

namespace App\Contrato\Application\Query\FindContratosFianzasPendientes;

use App\Contrato\Application\DTO\ContratoResponse;
use App\Contrato\Domain\Repository\ContratoRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class FindContratosFianzasPendientesQueryHandler
{
    public function __construct(
        private ContratoRepositoryInterface $contratoRepository
    ) {
    }

    /**
     * @return ContratoResponse[]
     */
    public function __invoke(FindContratosFianzasPendientesQuery $query): array
    {
        $contratos = $this->contratoRepository->findConFianzaPendiente();

        return array_map(
            fn($contrato) => ContratoResponse::fromContrato($contrato),
            $contratos
        );
    }
}
