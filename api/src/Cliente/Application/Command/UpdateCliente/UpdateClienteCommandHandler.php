<?php

declare(strict_types=1);

namespace App\Cliente\Application\Command\UpdateCliente;

use App\Cliente\Application\DTO\ClienteResponse;
use App\Cliente\Domain\Event\ClienteUpdated;
use App\Cliente\Domain\Exception\ClienteNotFoundException;
use App\Cliente\Domain\Exception\DuplicatedDniNieException;
use App\Cliente\Domain\Exception\DuplicatedEmailException;
use App\Cliente\Domain\Model\ClienteId;
use App\Cliente\Domain\Model\DniNie;
use App\Cliente\Domain\Model\Email;
use App\Cliente\Domain\Model\Telefono;
use App\Cliente\Domain\Repository\ClienteRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final readonly class UpdateClienteCommandHandler
{
    public function __construct(
        private ClienteRepositoryInterface $clienteRepository,
        private MessageBusInterface $eventBus
    ) {
    }

    public function __invoke(UpdateClienteCommand $command): ClienteResponse
    {
        $clienteId = ClienteId::fromInt($command->id);
        $cliente = $this->clienteRepository->findById($clienteId);

        if ($cliente === null) {
            throw ClienteNotFoundException::withId($command->id);
        }

        $dniNie = $command->dniNie !== null && trim($command->dniNie) !== ''
            ? DniNie::fromString($command->dniNie)
            : null;

        $email = $command->email !== null && trim($command->email) !== ''
            ? Email::fromString($command->email)
            : null;

        $telefono = $command->telefono !== null && trim($command->telefono) !== ''
            ? Telefono::fromString($command->telefono)
            : null;

        if ($dniNie !== null && $this->clienteRepository->existsByDniNie($dniNie->value, $command->id)) {
            throw DuplicatedDniNieException::withDniNie($dniNie->value);
        }

        if ($email !== null && $this->clienteRepository->existsByEmail($email->value, $command->id)) {
            throw DuplicatedEmailException::withEmail($email->value);
        }

        $cliente->update(
            nombre: $command->nombre,
            apellidos: $command->apellidos,
            dniNie: $dniNie,
            email: $email,
            telefono: $telefono,
            activo: $command->activo
        );

        $this->clienteRepository->save($cliente);

        $this->eventBus->dispatch(
            ClienteUpdated::create(
                clienteId: $command->id,
                nombre: $command->nombre,
                apellidos: $command->apellidos
            )
        );

        return ClienteResponse::fromCliente($cliente);
    }
}
