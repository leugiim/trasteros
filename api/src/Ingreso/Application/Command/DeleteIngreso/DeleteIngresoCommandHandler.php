<?php

declare(strict_types=1);

namespace App\Ingreso\Application\Command\DeleteIngreso;

use App\Ingreso\Domain\Exception\IngresoNotFoundException;
use App\Ingreso\Domain\Model\IngresoId;
use App\Ingreso\Domain\Repository\IngresoRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class DeleteIngresoCommandHandler
{
    public function __construct(
        private IngresoRepositoryInterface $ingresoRepository
    ) {
    }

    public function __invoke(DeleteIngresoCommand $command): void
    {
        $ingreso = $this->ingresoRepository->findById(IngresoId::fromInt($command->id));

        if ($ingreso === null) {
            throw IngresoNotFoundException::withId($command->id);
        }

        $this->ingresoRepository->remove($ingreso);
    }
}
