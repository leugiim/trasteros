<?php

declare(strict_types=1);

namespace App\Direccion\Application\Command\UpdateDireccion;

use App\Direccion\Application\DTO\DireccionResponse;
use App\Direccion\Domain\Event\DireccionUpdated;
use App\Direccion\Domain\Exception\DireccionNotFoundException;
use App\Direccion\Domain\Model\CodigoPostal;
use App\Direccion\Domain\Model\Coordenadas;
use App\Direccion\Domain\Model\DireccionId;
use App\Direccion\Domain\Repository\DireccionRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final readonly class UpdateDireccionCommandHandler
{
    public function __construct(
        private DireccionRepositoryInterface $direccionRepository,
        private MessageBusInterface $eventBus
    ) {
    }

    public function __invoke(UpdateDireccionCommand $command): DireccionResponse
    {
        $direccionId = DireccionId::fromInt($command->id);
        $direccion = $this->direccionRepository->findById($direccionId);

        if ($direccion === null) {
            throw DireccionNotFoundException::withId($command->id);
        }

        $codigoPostal = CodigoPostal::fromString($command->codigoPostal);
        $coordenadas = Coordenadas::create($command->latitud, $command->longitud);

        $direccion->update(
            nombreVia: $command->nombreVia,
            codigoPostal: $codigoPostal,
            ciudad: $command->ciudad,
            provincia: $command->provincia,
            pais: $command->pais,
            tipoVia: $command->tipoVia,
            numero: $command->numero,
            piso: $command->piso,
            puerta: $command->puerta,
            coordenadas: $coordenadas
        );

        $this->direccionRepository->save($direccion);

        $this->eventBus->dispatch(
            DireccionUpdated::create(direccionId: $command->id)
        );

        return DireccionResponse::fromDireccion($direccion);
    }
}
