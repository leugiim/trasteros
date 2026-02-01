<?php

declare(strict_types=1);

namespace App\Local\Application\Command\DeleteLocal;

use App\Local\Domain\Event\LocalDeleted;
use App\Local\Domain\Exception\LocalNotFoundException;
use App\Local\Domain\Model\LocalId;
use App\Local\Domain\Repository\LocalRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final readonly class DeleteLocalCommandHandler
{
    public function __construct(
        private LocalRepositoryInterface $localRepository,
        private MessageBusInterface $eventBus
    ) {
    }

    public function __invoke(DeleteLocalCommand $command): void
    {
        $localId = LocalId::fromInt($command->id);
        $local = $this->localRepository->findById($localId);

        if ($local === null) {
            throw LocalNotFoundException::withId($command->id);
        }

        $this->localRepository->remove($local);

        $this->eventBus->dispatch(
            LocalDeleted::create($command->id)
        );
    }
}
