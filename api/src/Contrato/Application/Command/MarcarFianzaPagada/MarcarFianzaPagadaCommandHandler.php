<?php

declare(strict_types=1);

namespace App\Contrato\Application\Command\MarcarFianzaPagada;

use App\Contrato\Application\DTO\ContratoResponse;
use App\Contrato\Domain\Exception\ContratoNotFoundException;
use App\Contrato\Domain\Repository\ContratoRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class MarcarFianzaPagadaCommandHandler
{
    public function __construct(
        private ContratoRepositoryInterface $contratoRepository
    ) {
    }

    public function __invoke(MarcarFianzaPagadaCommand $command): ContratoResponse
    {
        $contrato = $this->contratoRepository->findById($command->id);
        if ($contrato === null) {
            throw ContratoNotFoundException::withId($command->id);
        }

        $contrato->marcarFianzaPagada();
        $this->contratoRepository->save($contrato);

        return ContratoResponse::fromContrato($contrato);
    }
}
