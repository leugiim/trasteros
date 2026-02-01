<?php

declare(strict_types=1);

namespace App\Prestamo\Application\Query\FindPrestamo;

use App\Prestamo\Application\DTO\PrestamoResponse;
use App\Prestamo\Domain\Exception\PrestamoNotFoundException;
use App\Prestamo\Domain\Model\PrestamoId;
use App\Prestamo\Domain\Repository\PrestamoRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class FindPrestamoQueryHandler
{
    public function __construct(
        private PrestamoRepositoryInterface $prestamoRepository
    ) {
    }

    public function __invoke(FindPrestamoQuery $query): PrestamoResponse
    {
        $prestamo = $this->prestamoRepository->findById(PrestamoId::fromInt($query->id));

        if ($prestamo === null) {
            throw PrestamoNotFoundException::withId($query->id);
        }

        return PrestamoResponse::fromPrestamo($prestamo);
    }
}
