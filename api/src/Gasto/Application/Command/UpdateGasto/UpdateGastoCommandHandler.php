<?php

declare(strict_types=1);

namespace App\Gasto\Application\Command\UpdateGasto;

use App\Gasto\Application\DTO\GastoResponse;
use App\Gasto\Domain\Exception\GastoNotFoundException;
use App\Gasto\Domain\Exception\InvalidGastoCategoriaException;
use App\Gasto\Domain\Exception\InvalidMetodoPagoException;
use App\Gasto\Domain\Model\GastoCategoria;
use App\Gasto\Domain\Model\GastoId;
use App\Gasto\Domain\Model\Importe;
use App\Gasto\Domain\Model\MetodoPago;
use App\Gasto\Domain\Repository\GastoRepositoryInterface;
use App\Local\Domain\Exception\LocalNotFoundException;
use App\Local\Domain\Model\LocalId;
use App\Local\Domain\Repository\LocalRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class UpdateGastoCommandHandler
{
    public function __construct(
        private GastoRepositoryInterface $gastoRepository,
        private LocalRepositoryInterface $localRepository
    ) {
    }

    public function __invoke(UpdateGastoCommand $command): GastoResponse
    {
        $gasto = $this->gastoRepository->findById(GastoId::fromInt($command->id));

        if ($gasto === null) {
            throw GastoNotFoundException::withId($command->id);
        }

        $local = $this->localRepository->findById(LocalId::fromInt($command->localId));

        if ($local === null) {
            throw LocalNotFoundException::withId($command->localId);
        }

        try {
            $categoria = GastoCategoria::fromString($command->categoria);
        } catch (\ValueError $e) {
            throw InvalidGastoCategoriaException::withValue($command->categoria);
        }

        $metodoPago = null;
        if ($command->metodoPago !== null) {
            try {
                $metodoPago = MetodoPago::fromString($command->metodoPago);
            } catch (\ValueError $e) {
                throw InvalidMetodoPagoException::withValue($command->metodoPago);
            }
        }

        $fecha = \DateTimeImmutable::createFromFormat('Y-m-d', $command->fecha);
        if ($fecha === false) {
            throw new \InvalidArgumentException('Invalid date format. Expected Y-m-d');
        }

        $importe = Importe::fromFloat($command->importe);

        $gasto->update(
            local: $local,
            concepto: $command->concepto,
            importe: $importe,
            fecha: $fecha,
            categoria: $categoria,
            descripcion: $command->descripcion,
            metodoPago: $metodoPago
        );

        $this->gastoRepository->save($gasto);

        return GastoResponse::fromGasto($gasto);
    }
}
