<?php

declare(strict_types=1);

namespace App\Prestamo\Application\Command\UpdatePrestamo;

use App\Local\Domain\Exception\LocalNotFoundException;
use App\Local\Domain\Model\LocalId;
use App\Local\Domain\Repository\LocalRepositoryInterface;
use App\Gasto\Domain\Repository\GastoRepositoryInterface;
use App\Prestamo\Application\DTO\PrestamoResponse;
use App\Prestamo\Domain\Exception\InvalidPrestamoEstadoException;
use App\Prestamo\Domain\Exception\PrestamoNotFoundException;
use App\Prestamo\Domain\Model\CapitalSolicitado;
use App\Prestamo\Domain\Model\PrestamoEstado;
use App\Prestamo\Domain\Model\PrestamoId;
use App\Prestamo\Domain\Model\TipoInteres;
use App\Prestamo\Domain\Model\TotalADevolver;
use App\Prestamo\Domain\Repository\PrestamoRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class UpdatePrestamoCommandHandler
{
    public function __construct(
        private PrestamoRepositoryInterface $prestamoRepository,
        private LocalRepositoryInterface $localRepository,
        private GastoRepositoryInterface $gastoRepository
    ) {
    }

    public function __invoke(UpdatePrestamoCommand $command): PrestamoResponse
    {
        $prestamo = $this->prestamoRepository->findById(PrestamoId::fromInt($command->id));

        if ($prestamo === null) {
            throw PrestamoNotFoundException::withId($command->id);
        }

        $local = $this->localRepository->findById(LocalId::fromInt($command->localId));

        if ($local === null) {
            throw LocalNotFoundException::withId($command->localId);
        }

        try {
            $estado = PrestamoEstado::fromString($command->estado);
        } catch (\ValueError $e) {
            throw InvalidPrestamoEstadoException::withValue($command->estado);
        }

        $fechaConcesion = \DateTimeImmutable::createFromFormat('Y-m-d', $command->fechaConcesion);
        if ($fechaConcesion === false) {
            throw new \InvalidArgumentException('Invalid date format. Expected Y-m-d');
        }

        $capitalSolicitado = CapitalSolicitado::fromFloat($command->capitalSolicitado);
        $totalADevolver = TotalADevolver::fromFloat($command->totalADevolver);
        $tipoInteres = $command->tipoInteres !== null ? TipoInteres::fromFloat($command->tipoInteres) : null;

        $prestamo->update(
            local: $local,
            capitalSolicitado: $capitalSolicitado,
            totalADevolver: $totalADevolver,
            fechaConcesion: $fechaConcesion,
            entidadBancaria: $command->entidadBancaria,
            numeroPrestamo: $command->numeroPrestamo,
            tipoInteres: $tipoInteres,
            estado: $estado
        );

        $this->prestamoRepository->save($prestamo);

        $amortizado = $this->gastoRepository->getTotalImporteByPrestamoId($command->id);

        return PrestamoResponse::fromPrestamo($prestamo, $amortizado);
    }
}
