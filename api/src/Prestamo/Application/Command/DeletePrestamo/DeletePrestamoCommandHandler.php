<?php

declare(strict_types=1);

namespace App\Prestamo\Application\Command\DeletePrestamo;

use App\Prestamo\Domain\Exception\PrestamoNotFoundException;
use App\Prestamo\Domain\Model\PrestamoId;
use App\Prestamo\Domain\Repository\PrestamoRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class DeletePrestamoCommandHandler
{
    public function __construct(
        private PrestamoRepositoryInterface $prestamoRepository
    ) {
    }

    public function __invoke(DeletePrestamoCommand $command): void
    {
        $prestamo = $this->prestamoRepository->findById(PrestamoId::fromInt($command->id));

        if ($prestamo === null) {
            throw PrestamoNotFoundException::withId($command->id);
        }

        // Soft delete - For now, we'll use a dummy user. In production, get from security context
        // $prestamo->softDelete($currentUser);

        // Hard delete for now
        $this->prestamoRepository->remove($prestamo);
    }
}
