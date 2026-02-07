<?php

declare(strict_types=1);

namespace App\Trastero\Application\Command\UpdateTrastero;

use App\Contrato\Domain\Repository\ContratoRepositoryInterface;
use App\Local\Domain\Exception\LocalNotFoundException;
use App\Local\Domain\Model\LocalId;
use App\Local\Domain\Repository\LocalRepositoryInterface;
use App\Trastero\Application\DTO\TrasteroResponse;
use App\Trastero\Domain\Event\TrasteroEstadoChanged;
use App\Trastero\Domain\Event\TrasteroUpdated;
use App\Trastero\Domain\Exception\DuplicateTrasteroException;
use App\Trastero\Domain\Exception\InvalidTrasteroEstadoException;
use App\Trastero\Domain\Exception\TrasteroNotFoundException;
use App\Trastero\Domain\Model\PrecioMensual;
use App\Trastero\Domain\Model\Superficie;
use App\Trastero\Domain\Model\TrasteroEstado;
use App\Trastero\Domain\Model\TrasteroId;
use App\Trastero\Domain\Repository\TrasteroRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AsMessageHandler]
final readonly class UpdateTrasteroCommandHandler
{
    public function __construct(
        private TrasteroRepositoryInterface $trasteroRepository,
        private LocalRepositoryInterface $localRepository,
        private ContratoRepositoryInterface $contratoRepository,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function __invoke(UpdateTrasteroCommand $command): TrasteroResponse
    {
        $trastero = $this->trasteroRepository->findById(TrasteroId::fromInt($command->id));
        if ($trastero === null) {
            throw TrasteroNotFoundException::withId($command->id);
        }

        $local = $this->localRepository->findById(LocalId::fromInt($command->localId));
        if ($local === null) {
            throw LocalNotFoundException::withId($command->localId);
        }

        $existingTrastero = $this->trasteroRepository->findByNumeroAndLocal($command->numero, $command->localId);
        if ($existingTrastero !== null && !$existingTrastero->id()->equals($trastero->id())) {
            throw DuplicateTrasteroException::withNumeroAndLocal($command->numero, $command->localId);
        }

        $estado = TrasteroEstado::tryFromString($command->estado);
        if ($estado === null) {
            throw InvalidTrasteroEstadoException::invalidValue($command->estado);
        }

        $previousEstado = $trastero->estado();

        $trastero->update(
            local: $local,
            numero: $command->numero,
            superficie: Superficie::fromFloat($command->superficie),
            precioMensual: PrecioMensual::fromFloat($command->precioMensual),
            nombre: $command->nombre,
            estado: $estado
        );

        $this->trasteroRepository->save($trastero);

        $this->eventDispatcher->dispatch(new TrasteroUpdated(
            trasteroId: $trastero->id()->value,
            localId: $trastero->local()->id()->value,
            numero: $trastero->numero()
        ));

        if ($previousEstado !== $estado) {
            $this->eventDispatcher->dispatch(new TrasteroEstadoChanged(
                trasteroId: $trastero->id()->value,
                previousEstado: $previousEstado->value,
                newEstado: $estado->value
            ));
        }

        $contratos = $this->contratoRepository->findByTrasteroId($trastero->id()->value);

        return TrasteroResponse::fromTrasteroWithContratos($trastero, $contratos);
    }
}
