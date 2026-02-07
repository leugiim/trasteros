<?php

declare(strict_types=1);

namespace App\Contrato\Application\Command\FinalizarContrato;

use App\Contrato\Application\DTO\ContratoResponse;
use App\Contrato\Domain\Event\ContratoFinalizado;
use App\Contrato\Domain\Exception\ContratoNotFoundException;
use App\Contrato\Domain\Repository\ContratoRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final readonly class FinalizarContratoCommandHandler
{
    public function __construct(
        private ContratoRepositoryInterface $contratoRepository,
        private MessageBusInterface $eventBus
    ) {
    }

    public function __invoke(FinalizarContratoCommand $command): ContratoResponse
    {
        $contrato = $this->contratoRepository->findById($command->id);
        if ($contrato === null) {
            throw ContratoNotFoundException::withId($command->id);
        }

        $contrato->finalizarAnticipadamente();
        $this->contratoRepository->save($contrato);

        $this->eventBus->dispatch(new ContratoFinalizado(
            contratoId: $contrato->id()->value,
            trasteroId: $contrato->trastero()->id()->value
        ));

        return ContratoResponse::fromContrato($contrato);
    }
}
