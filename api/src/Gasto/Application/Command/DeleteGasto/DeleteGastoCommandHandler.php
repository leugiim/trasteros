<?php

declare(strict_types=1);

namespace App\Gasto\Application\Command\DeleteGasto;

use App\Gasto\Domain\Exception\GastoNotFoundException;
use App\Gasto\Domain\Model\GastoId;
use App\Gasto\Domain\Repository\GastoRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class DeleteGastoCommandHandler
{
    public function __construct(
        private GastoRepositoryInterface $gastoRepository
    ) {
    }

    public function __invoke(DeleteGastoCommand $command): void
    {
        $gasto = $this->gastoRepository->findById(GastoId::fromInt($command->id));

        if ($gasto === null) {
            throw GastoNotFoundException::withId($command->id);
        }

        // Soft delete - en producción podrías pasar el usuario autenticado
        // $gasto->softDelete($currentUser);
        // Por ahora solo marcamos como eliminado
        $this->gastoRepository->remove($gasto);
    }
}
