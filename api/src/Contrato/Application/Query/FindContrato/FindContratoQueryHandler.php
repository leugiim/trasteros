<?php

declare(strict_types=1);

namespace App\Contrato\Application\Query\FindContrato;

use App\Contrato\Application\DTO\ContratoResponse;
use App\Contrato\Domain\Exception\ContratoNotFoundException;
use App\Contrato\Domain\Repository\ContratoRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class FindContratoQueryHandler
{
    public function __construct(
        private ContratoRepositoryInterface $contratoRepository
    ) {
    }

    public function __invoke(FindContratoQuery $query): ContratoResponse
    {
        $contrato = $this->contratoRepository->findById($query->id);
        if ($contrato === null) {
            throw new ContratoNotFoundException($query->id);
        }

        return ContratoResponse::fromContrato($contrato);
    }
}
