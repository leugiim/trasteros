<?php

declare(strict_types=1);

namespace App\Contrato\Application\Query\ListContratos;

use App\Contrato\Application\DTO\ContratoResponse;
use App\Contrato\Domain\Model\ContratoEstado;
use App\Contrato\Domain\Repository\ContratoRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ListContratosQueryHandler
{
    public function __construct(
        private ContratoRepositoryInterface $contratoRepository
    ) {
    }

    public function __invoke(ListContratosQuery $query): array
    {
        if ($query->estado !== null) {
            $estado = ContratoEstado::fromString($query->estado);
            $contratos = $this->contratoRepository->findByEstado($estado);
        } else {
            $contratos = $this->contratoRepository->findAll();
        }

        return array_map(
            fn($contrato) => ContratoResponse::fromContrato($contrato),
            $contratos
        );
    }
}
