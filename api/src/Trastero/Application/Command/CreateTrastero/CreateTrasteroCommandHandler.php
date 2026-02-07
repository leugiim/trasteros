<?php

declare(strict_types=1);

namespace App\Trastero\Application\Command\CreateTrastero;

use App\Local\Domain\Exception\LocalNotFoundException;
use App\Local\Domain\Model\LocalId;
use App\Local\Domain\Repository\LocalRepositoryInterface;
use App\Trastero\Application\DTO\TrasteroResponse;
use App\Trastero\Domain\Event\TrasteroCreated;
use App\Trastero\Domain\Exception\DuplicateTrasteroException;
use App\Trastero\Domain\Exception\InvalidTrasteroEstadoException;
use App\Trastero\Domain\Model\PrecioMensual;
use App\Trastero\Domain\Model\Superficie;
use App\Trastero\Domain\Model\Trastero;
use App\Trastero\Domain\Model\TrasteroEstado;
use App\Trastero\Domain\Repository\TrasteroRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AsMessageHandler]
final readonly class CreateTrasteroCommandHandler
{
    public function __construct(
        private TrasteroRepositoryInterface $trasteroRepository,
        private LocalRepositoryInterface $localRepository,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function __invoke(CreateTrasteroCommand $command): TrasteroResponse
    {
        $local = $this->localRepository->findById(LocalId::fromInt($command->localId));
        if ($local === null) {
            throw LocalNotFoundException::withId($command->localId);
        }

        if ($this->trasteroRepository->existsByNumeroAndLocal($command->numero, $command->localId)) {
            throw DuplicateTrasteroException::withNumeroAndLocal($command->numero, $command->localId);
        }

        $estado = TrasteroEstado::tryFromString($command->estado);
        if ($estado === null) {
            throw InvalidTrasteroEstadoException::invalidValue($command->estado);
        }

        $trastero = Trastero::create(
            local: $local,
            numero: $command->numero,
            superficie: Superficie::fromFloat($command->superficie),
            precioMensual: PrecioMensual::fromFloat($command->precioMensual),
            nombre: $command->nombre,
            estado: $estado
        );

        $this->trasteroRepository->save($trastero);

        $this->eventDispatcher->dispatch(new TrasteroCreated(
            trasteroId: $trastero->id()->value,
            localId: $trastero->local()->id()->value,
            numero: $trastero->numero()
        ));

        return TrasteroResponse::fromTrasteroWithContratos($trastero, []);
    }
}
