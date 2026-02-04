<?php

declare(strict_types=1);

namespace App\Contrato\Application\Command\CancelarContrato;

use App\Contrato\Application\DTO\ContratoResponse;
use App\Contrato\Domain\Event\ContratoCancelado;
use App\Contrato\Domain\Exception\ContratoNotFoundException;
use App\Contrato\Domain\Repository\ContratoRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final readonly class CancelarContratoCommandHandler
{
    public function __construct(
        private ContratoRepositoryInterface $contratoRepository,
        private MessageBusInterface $eventBus
    ) {
    }

    public function __invoke(CancelarContratoCommand $command): ContratoResponse
    {
        $contrato = $this->contratoRepository->findById($command->id);
        if ($contrato === null) {
            throw ContratoNotFoundException::withId($command->id);
        }

        $contrato->cancelar();
        $this->contratoRepository->save($contrato);

        $this->eventBus->dispatch(new ContratoCancelado(
            contratoId: $contrato->id()->value,
            trasteroId: $contrato->trastero()->id()->value
        ));

        return ContratoResponse::fromContrato($contrato);
    }
}
