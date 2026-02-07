<?php

declare(strict_types=1);

namespace App\Contrato\Application\Command\CreateContrato;

use App\Cliente\Domain\Exception\ClienteNotFoundException;
use App\Cliente\Domain\Model\ClienteId;
use App\Cliente\Domain\Repository\ClienteRepositoryInterface;
use App\Contrato\Application\DTO\ContratoResponse;
use App\Contrato\Domain\Event\ContratoCreated;
use App\Contrato\Domain\Exception\InvalidContratoDateException;
use App\Contrato\Domain\Exception\TrasteroAlreadyRentedException;
use App\Contrato\Domain\Model\Contrato;
use App\Contrato\Domain\Model\Fianza;
use App\Contrato\Domain\Model\PrecioMensual;
use App\Contrato\Domain\Repository\ContratoRepositoryInterface;
use App\Trastero\Domain\Exception\TrasteroNotFoundException;
use App\Trastero\Domain\Model\TrasteroId;
use App\Trastero\Domain\Repository\TrasteroRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final readonly class CreateContratoCommandHandler
{
    public function __construct(
        private ContratoRepositoryInterface $contratoRepository,
        private TrasteroRepositoryInterface $trasteroRepository,
        private ClienteRepositoryInterface $clienteRepository,
        private MessageBusInterface $eventBus
    ) {
    }

    public function __invoke(CreateContratoCommand $command): ContratoResponse
    {
        $trasteroId = TrasteroId::fromInt($command->trasteroId);
        $trastero = $this->trasteroRepository->findById($trasteroId);
        if ($trastero === null) {
            throw TrasteroNotFoundException::withId($command->trasteroId);
        }

        $clienteId = ClienteId::fromInt($command->clienteId);
        $cliente = $this->clienteRepository->findById($clienteId);
        if ($cliente === null) {
            throw ClienteNotFoundException::withId($command->clienteId);
        }

        $fechaInicio = new \DateTimeImmutable($command->fechaInicio);
        $fechaFin = $command->fechaFin !== null ? new \DateTimeImmutable($command->fechaFin) : null;

        if ($fechaFin !== null && $fechaFin < $fechaInicio) {
            throw new InvalidContratoDateException('La fecha de fin debe ser posterior a la fecha de inicio');
        }

        $solapados = $this->contratoRepository->findContratosSolapados(
            $trasteroId->value,
            $fechaInicio,
            $fechaFin
        );

        if (count($solapados) > 0) {
            throw new TrasteroAlreadyRentedException($command->trasteroId);
        }

        $precioMensual = PrecioMensual::fromFloat($command->precioMensual);
        $fianza = $command->fianza !== null ? Fianza::fromFloat($command->fianza) : null;

        $contrato = Contrato::create(
            trastero: $trastero,
            cliente: $cliente,
            fechaInicio: $fechaInicio,
            precioMensual: $precioMensual,
            fechaFin: $fechaFin,
            fianza: $fianza,
            fianzaPagada: $command->fianzaPagada
        );

        $this->contratoRepository->save($contrato);

        $this->eventBus->dispatch(new ContratoCreated(
            contratoId: $contrato->id()->value,
            trasteroId: $trastero->id()->value,
            clienteId: $cliente->id()->value
        ));

        return ContratoResponse::fromContrato($contrato);
    }
}
