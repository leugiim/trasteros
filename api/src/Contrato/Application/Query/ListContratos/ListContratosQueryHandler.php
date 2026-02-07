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
        $contratos = $this->contratoRepository->findAll();

        if ($query->estado !== null) {
            $estadoFiltro = ContratoEstado::fromString($query->estado);
            $contratos = array_filter(
                $contratos,
                fn($contrato) => $contrato->estadoCalculado() === $estadoFiltro
            );
        }

        return array_map(
            fn($contrato) => ContratoResponse::fromContrato($contrato),
            array_values($contratos)
        );
    }
}
