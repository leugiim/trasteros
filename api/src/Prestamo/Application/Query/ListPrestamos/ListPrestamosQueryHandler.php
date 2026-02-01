<?php

declare(strict_types=1);

namespace App\Prestamo\Application\Query\ListPrestamos;

use App\Prestamo\Application\DTO\PrestamoResponse;
use App\Prestamo\Domain\Exception\InvalidPrestamoEstadoException;
use App\Prestamo\Domain\Model\PrestamoEstado;
use App\Prestamo\Domain\Repository\PrestamoRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ListPrestamosQueryHandler
{
    public function __construct(
        private PrestamoRepositoryInterface $prestamoRepository
    ) {
    }

    /**
     * @return PrestamoResponse[]
     */
    public function __invoke(ListPrestamosQuery $query): array
    {
        if ($query->localId !== null) {
            $prestamos = $this->prestamoRepository->findByLocalId($query->localId);
        } elseif ($query->estado !== null) {
            try {
                PrestamoEstado::fromString($query->estado);
            } catch (\ValueError $e) {
                throw InvalidPrestamoEstadoException::withValue($query->estado);
            }
            $prestamos = $this->prestamoRepository->findByEstado($query->estado);
        } elseif ($query->entidadBancaria !== null) {
            $prestamos = $this->prestamoRepository->findByEntidadBancaria($query->entidadBancaria);
        } elseif ($query->onlyActive === true) {
            $prestamos = $this->prestamoRepository->findActivePrestamos();
        } else {
            $prestamos = $this->prestamoRepository->findAll();
        }

        return array_map(
            fn($prestamo) => PrestamoResponse::fromPrestamo($prestamo),
            $prestamos
        );
    }
}
