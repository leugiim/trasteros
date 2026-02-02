<?php

declare(strict_types=1);

namespace App\Prestamo\Application\Query\FindPrestamosByLocal;

use App\Prestamo\Application\DTO\PrestamoResponse;
use App\Prestamo\Domain\Repository\PrestamoRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class FindPrestamosByLocalQueryHandler
{
    public function __construct(
        private PrestamoRepositoryInterface $prestamoRepository
    ) {
    }

    /**
     * @return PrestamoResponse[]
     */
    public function __invoke(FindPrestamosByLocalQuery $query): array
    {
        $prestamos = $this->prestamoRepository->findByLocalId($query->localId);

        return array_map(
            fn($prestamo) => PrestamoResponse::fromPrestamo($prestamo),
            $prestamos
        );
    }
}
