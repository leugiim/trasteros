<?php

declare(strict_types=1);

namespace App\Direccion\Application\Command\CreateDireccion;

use App\Direccion\Application\DTO\DireccionResponse;
use App\Direccion\Domain\Event\DireccionCreated;
use App\Direccion\Domain\Model\CodigoPostal;
use App\Direccion\Domain\Model\Coordenadas;
use App\Direccion\Domain\Model\Direccion;
use App\Direccion\Domain\Repository\DireccionRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final readonly class CreateDireccionCommandHandler
{
    public function __construct(
        private DireccionRepositoryInterface $direccionRepository,
        private MessageBusInterface $eventBus
    ) {
    }

    public function __invoke(CreateDireccionCommand $command): DireccionResponse
    {
        $codigoPostal = CodigoPostal::fromString($command->codigoPostal);
        $coordenadas = Coordenadas::create($command->latitud, $command->longitud);

        $direccion = Direccion::create(
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

        $direccionId = $direccion->id();
        if ($direccionId !== null) {
            $this->eventBus->dispatch(
                DireccionCreated::create(
                    direccionId: $direccionId->value,
                    nombreVia: $command->nombreVia,
                    ciudad: $command->ciudad,
                    provincia: $command->provincia,
                    codigoPostal: $command->codigoPostal
                )
            );
        }

        return DireccionResponse::fromDireccion($direccion);
    }
}
