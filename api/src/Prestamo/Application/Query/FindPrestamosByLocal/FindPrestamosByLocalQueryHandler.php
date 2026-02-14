<?php

declare(strict_types=1);

namespace App\Prestamo\Application\Query\FindPrestamosByLocal;

use App\Gasto\Domain\Repository\GastoRepositoryInterface;
use App\Prestamo\Application\DTO\PrestamoResponse;
use App\Prestamo\Domain\Repository\PrestamoRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class FindPrestamosByLocalQueryHandler
{
    public function __construct(
        private PrestamoRepositoryInterface $prestamoRepository,
        private GastoRepositoryInterface $gastoRepository
    ) {
    }

    /**
     * @return PrestamoResponse[]
     */
    public function __invoke(FindPrestamosByLocalQuery $query): array
    {
        $prestamos = $this->prestamoRepository->findByLocalId($query->localId);

        $prestamoIds = array_filter(array_map(
            fn($p) => $p->id()?->value,
            $prestamos
        ));
        $amortizadoMap = $this->gastoRepository->getTotalImporteGroupedByPrestamo($prestamoIds);

        return array_map(
            fn($prestamo) => PrestamoResponse::fromPrestamo(
                $prestamo,
                $amortizadoMap[$prestamo->id()->value] ?? 0.0
            ),
            $prestamos
        );
    }
}
