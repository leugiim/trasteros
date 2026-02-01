<?php

declare(strict_types=1);

namespace App\Contrato\Application\Query\FindContratosByCliente;

use App\Contrato\Application\DTO\ContratoResponse;
use App\Contrato\Domain\Repository\ContratoRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class FindContratosByClienteQueryHandler
{
    public function __construct(
        private ContratoRepositoryInterface $contratoRepository
    ) {
    }

    public function __invoke(FindContratosByClienteQuery $query): array
    {
        if ($query->onlyActivos) {
            $contratos = $this->contratoRepository->findContratosActivosByCliente($query->clienteId);
        } else {
            $contratos = $this->contratoRepository->findByClienteId($query->clienteId);
        }

        return array_map(
            fn($contrato) => ContratoResponse::fromContrato($contrato),
            $contratos
        );
    }
}
