<?php

declare(strict_types=1);

namespace App\Local\Application\Command\UpdateLocal;

use App\Direccion\Domain\Exception\DireccionNotFoundException;
use App\Direccion\Domain\Model\DireccionId;
use App\Direccion\Domain\Repository\DireccionRepositoryInterface;
use App\Local\Application\DTO\LocalResponse;
use App\Local\Domain\Event\LocalUpdated;
use App\Local\Domain\Exception\LocalNotFoundException;
use App\Local\Domain\Model\LocalId;
use App\Local\Domain\Model\ReferenciaCatastral;
use App\Local\Domain\Repository\LocalRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final readonly class UpdateLocalCommandHandler
{
    public function __construct(
        private LocalRepositoryInterface $localRepository,
        private DireccionRepositoryInterface $direccionRepository,
        private MessageBusInterface $eventBus
    ) {
    }

    public function __invoke(UpdateLocalCommand $command): LocalResponse
    {
        $localId = LocalId::fromInt($command->id);
        $local = $this->localRepository->findById($localId);

        if ($local === null) {
            throw LocalNotFoundException::withId($command->id);
        }

        $direccionId = DireccionId::fromInt($command->direccionId);
        $direccion = $this->direccionRepository->findById($direccionId);

        if ($direccion === null) {
            throw DireccionNotFoundException::withId($command->direccionId);
        }

        $referenciaCatastral = $command->referenciaCatastral !== null
            ? ReferenciaCatastral::fromString($command->referenciaCatastral)
            : null;

        $fechaCompra = $command->fechaCompra !== null
            ? new \DateTimeImmutable($command->fechaCompra)
            : null;

        $local->update(
            nombre: $command->nombre,
            direccion: $direccion,
            superficieTotal: $command->superficieTotal,
            numeroTrasteros: $command->numeroTrasteros,
            fechaCompra: $fechaCompra,
            precioCompra: $command->precioCompra,
            referenciaCatastral: $referenciaCatastral,
            valorCatastral: $command->valorCatastral
        );

        $this->localRepository->save($local);

        $this->eventBus->dispatch(
            LocalUpdated::create(
                localId: $command->id,
                nombre: $command->nombre,
                direccionId: $command->direccionId
            )
        );

        return LocalResponse::fromLocal($local);
    }
}
