<?php

declare(strict_types=1);

namespace App\Direccion\Application\Command\DeleteDireccion;

use App\Direccion\Domain\Event\DireccionDeleted;
use App\Direccion\Domain\Exception\DireccionNotFoundException;
use App\Direccion\Domain\Model\DireccionId;
use App\Direccion\Domain\Repository\DireccionRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final readonly class DeleteDireccionCommandHandler
{
    public function __construct(
        private DireccionRepositoryInterface $direccionRepository,
        private MessageBusInterface $eventBus
    ) {
    }

    public function __invoke(DeleteDireccionCommand $command): void
    {
        $direccionId = DireccionId::fromInt($command->id);
        $direccion = $this->direccionRepository->findById($direccionId);

        if ($direccion === null) {
            throw DireccionNotFoundException::withId($command->id);
        }

        $this->direccionRepository->remove($direccion);

        $this->eventBus->dispatch(
            DireccionDeleted::create(direccionId: $command->id)
        );
    }
}
