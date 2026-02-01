<?php

declare(strict_types=1);

namespace App\Local\Application\Command\CreateLocal;

use App\Direccion\Domain\Exception\DireccionNotFoundException;
use App\Direccion\Domain\Model\DireccionId;
use App\Direccion\Domain\Repository\DireccionRepositoryInterface;
use App\Local\Application\DTO\LocalResponse;
use App\Local\Domain\Event\LocalCreated;
use App\Local\Domain\Model\Local;
use App\Local\Domain\Model\ReferenciaCatastral;
use App\Local\Domain\Repository\LocalRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final readonly class CreateLocalCommandHandler
{
    public function __construct(
        private LocalRepositoryInterface $localRepository,
        private DireccionRepositoryInterface $direccionRepository,
        private MessageBusInterface $eventBus
    ) {
    }

    public function __invoke(CreateLocalCommand $command): LocalResponse
    {
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

        $local = Local::create(
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

        $localId = $local->id();
        if ($localId !== null) {
            $this->eventBus->dispatch(
                LocalCreated::create(
                    localId: $localId->value,
                    nombre: $command->nombre,
                    direccionId: $command->direccionId
                )
            );
        }

        return LocalResponse::fromLocal($local);
    }
}
