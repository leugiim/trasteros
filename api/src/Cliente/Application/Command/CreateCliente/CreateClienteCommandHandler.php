<?php

declare(strict_types=1);

namespace App\Cliente\Application\Command\CreateCliente;

use App\Cliente\Application\DTO\ClienteResponse;
use App\Cliente\Domain\Event\ClienteCreated;
use App\Cliente\Domain\Exception\DuplicatedDniNieException;
use App\Cliente\Domain\Exception\DuplicatedEmailException;
use App\Cliente\Domain\Model\Cliente;
use App\Cliente\Domain\Model\DniNie;
use App\Cliente\Domain\Model\Email;
use App\Cliente\Domain\Model\Telefono;
use App\Cliente\Domain\Repository\ClienteRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final readonly class CreateClienteCommandHandler
{
    public function __construct(
        private ClienteRepositoryInterface $clienteRepository,
        private MessageBusInterface $eventBus
    ) {
    }

    public function __invoke(CreateClienteCommand $command): ClienteResponse
    {
        $dniNie = $command->dniNie !== null && trim($command->dniNie) !== ''
            ? DniNie::fromString($command->dniNie)
            : null;

        $email = $command->email !== null && trim($command->email) !== ''
            ? Email::fromString($command->email)
            : null;

        $telefono = $command->telefono !== null && trim($command->telefono) !== ''
            ? Telefono::fromString($command->telefono)
            : null;

        if ($dniNie !== null && $this->clienteRepository->existsByDniNie($dniNie->value)) {
            throw DuplicatedDniNieException::withDniNie($dniNie->value);
        }

        if ($email !== null && $this->clienteRepository->existsByEmail($email->value)) {
            throw DuplicatedEmailException::withEmail($email->value);
        }

        $cliente = Cliente::create(
            nombre: $command->nombre,
            apellidos: $command->apellidos,
            dniNie: $dniNie,
            email: $email,
            telefono: $telefono,
            activo: $command->activo
        );

        $this->clienteRepository->save($cliente);

        $clienteId = $cliente->id();
        if ($clienteId !== null) {
            $this->eventBus->dispatch(
                ClienteCreated::create(
                    clienteId: $clienteId->value,
                    nombre: $command->nombre,
                    apellidos: $command->apellidos,
                    email: $email?->value
                )
            );
        }

        return ClienteResponse::fromCliente($cliente);
    }
}
