<?php

declare(strict_types=1);

namespace App\Cliente\Application\Command\DeleteCliente;

use App\Cliente\Domain\Event\ClienteDeleted;
use App\Cliente\Domain\Exception\ClienteNotFoundException;
use App\Cliente\Domain\Model\ClienteId;
use App\Cliente\Domain\Repository\ClienteRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final readonly class DeleteClienteCommandHandler
{
    public function __construct(
        private ClienteRepositoryInterface $clienteRepository,
        private MessageBusInterface $eventBus
    ) {
    }

    public function __invoke(DeleteClienteCommand $command): void
    {
        $clienteId = ClienteId::fromInt($command->id);
        $cliente = $this->clienteRepository->findById($clienteId);

        if ($cliente === null) {
            throw ClienteNotFoundException::withId($command->id);
        }

        $this->clienteRepository->remove($cliente);

        $this->eventBus->dispatch(
            ClienteDeleted::create($command->id)
        );
    }
}
