<?php

declare(strict_types=1);

namespace App\Contrato\Application\Command\UpdateContrato;

use App\Cliente\Domain\Exception\ClienteNotFoundException;
use App\Cliente\Domain\Model\ClienteId;
use App\Cliente\Domain\Repository\ClienteRepositoryInterface;
use App\Contrato\Application\DTO\ContratoResponse;
use App\Contrato\Domain\Exception\ContratoNotFoundException;
use App\Contrato\Domain\Exception\InvalidContratoDateException;
use App\Contrato\Domain\Exception\TrasteroAlreadyRentedException;
use App\Contrato\Domain\Model\ContratoEstado;
use App\Contrato\Domain\Model\Fianza;
use App\Contrato\Domain\Model\PrecioMensual;
use App\Contrato\Domain\Repository\ContratoRepositoryInterface;
use App\Trastero\Domain\Exception\TrasteroNotFoundException;
use App\Trastero\Domain\Model\TrasteroId;
use App\Trastero\Domain\Repository\TrasteroRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class UpdateContratoCommandHandler
{
    public function __construct(
        private ContratoRepositoryInterface $contratoRepository,
        private TrasteroRepositoryInterface $trasteroRepository,
        private ClienteRepositoryInterface $clienteRepository
    ) {
    }

    public function __invoke(UpdateContratoCommand $command): ContratoResponse
    {
        $contrato = $this->contratoRepository->findById($command->id);
        if ($contrato === null) {
            throw ContratoNotFoundException::withId($command->id);
        }

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

        if ($contrato->trastero()->id()->value !== $command->trasteroId) {
            if ($this->contratoRepository->hasContratoActivoTrastero($command->trasteroId)) {
                throw new TrasteroAlreadyRentedException($command->trasteroId);
            }
        }

        $fechaInicio = new \DateTimeImmutable($command->fechaInicio);
        $fechaFin = $command->fechaFin !== null ? new \DateTimeImmutable($command->fechaFin) : null;

        if ($fechaFin !== null && $fechaFin < $fechaInicio) {
            throw new InvalidContratoDateException('La fecha de fin debe ser posterior a la fecha de inicio');
        }

        $precioMensual = PrecioMensual::fromFloat($command->precioMensual);
        $fianza = $command->fianza !== null ? Fianza::fromFloat($command->fianza) : null;
        $estado = ContratoEstado::fromString($command->estado);

        $contrato->update(
            trastero: $trastero,
            cliente: $cliente,
            fechaInicio: $fechaInicio,
            precioMensual: $precioMensual,
            fechaFin: $fechaFin,
            fianza: $fianza,
            fianzaPagada: $command->fianzaPagada,
            estado: $estado
        );

        $this->contratoRepository->save($contrato);

        return ContratoResponse::fromContrato($contrato);
    }
}
