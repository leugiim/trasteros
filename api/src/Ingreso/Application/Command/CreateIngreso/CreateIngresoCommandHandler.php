<?php

declare(strict_types=1);

namespace App\Ingreso\Application\Command\CreateIngreso;

use App\Contrato\Domain\Exception\ContratoNotFoundException;
use App\Contrato\Domain\Repository\ContratoRepositoryInterface;
use App\Ingreso\Application\DTO\IngresoResponse;
use App\Ingreso\Domain\Exception\InvalidIngresoCategoriaException;
use App\Ingreso\Domain\Exception\InvalidMetodoPagoException;
use App\Ingreso\Domain\Model\Importe;
use App\Ingreso\Domain\Model\Ingreso;
use App\Ingreso\Domain\Model\IngresoCategoria;
use App\Ingreso\Domain\Model\MetodoPago;
use App\Ingreso\Domain\Repository\IngresoRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class CreateIngresoCommandHandler
{
    public function __construct(
        private IngresoRepositoryInterface $ingresoRepository,
        private ContratoRepositoryInterface $contratoRepository
    ) {
    }

    public function __invoke(CreateIngresoCommand $command): IngresoResponse
    {
        $contrato = $this->contratoRepository->findById($command->contratoId);

        if ($contrato === null) {
            throw ContratoNotFoundException::withId($command->contratoId);
        }

        try {
            $categoria = IngresoCategoria::fromString($command->categoria);
        } catch (\ValueError $e) {
            throw InvalidIngresoCategoriaException::withValue($command->categoria);
        }

        $metodoPago = null;
        if ($command->metodoPago !== null) {
            try {
                $metodoPago = MetodoPago::fromString($command->metodoPago);
            } catch (\ValueError $e) {
                throw InvalidMetodoPagoException::withValue($command->metodoPago);
            }
        }

        $fechaPago = \DateTimeImmutable::createFromFormat('Y-m-d', $command->fechaPago);
        if ($fechaPago === false) {
            throw new \InvalidArgumentException('Invalid date format. Expected Y-m-d');
        }

        $importe = Importe::fromFloat($command->importe);

        $ingreso = Ingreso::create(
            contrato: $contrato,
            concepto: $command->concepto,
            importe: $importe,
            fechaPago: $fechaPago,
            categoria: $categoria,
            metodoPago: $metodoPago
        );

        $this->ingresoRepository->save($ingreso);

        return IngresoResponse::fromIngreso($ingreso);
    }
}
