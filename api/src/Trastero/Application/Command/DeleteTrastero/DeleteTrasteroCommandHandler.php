<?php

declare(strict_types=1);

namespace App\Trastero\Application\Command\DeleteTrastero;

use App\Trastero\Domain\Event\TrasteroDeleted;
use App\Trastero\Domain\Exception\TrasteroNotFoundException;
use App\Trastero\Domain\Model\TrasteroId;
use App\Trastero\Domain\Repository\TrasteroRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AsMessageHandler]
final readonly class DeleteTrasteroCommandHandler
{
    public function __construct(
        private TrasteroRepositoryInterface $trasteroRepository,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function __invoke(DeleteTrasteroCommand $command): void
    {
        $trastero = $this->trasteroRepository->findById(TrasteroId::fromInt($command->id));
        if ($trastero === null) {
            throw TrasteroNotFoundException::withId($command->id);
        }

        $this->eventDispatcher->dispatch(new TrasteroDeleted(
            trasteroId: $trastero->id()->value,
            localId: $trastero->local()->id()->value,
            numero: $trastero->numero()
        ));

        $this->trasteroRepository->remove($trastero);
    }
}
