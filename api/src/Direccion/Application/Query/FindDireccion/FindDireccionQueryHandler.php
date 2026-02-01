<?php

declare(strict_types=1);

namespace App\Direccion\Application\Query\FindDireccion;

use App\Direccion\Application\DTO\DireccionResponse;
use App\Direccion\Domain\Exception\DireccionNotFoundException;
use App\Direccion\Domain\Model\DireccionId;
use App\Direccion\Domain\Repository\DireccionRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class FindDireccionQueryHandler
{
    public function __construct(
        private DireccionRepositoryInterface $direccionRepository
    ) {
    }

    public function __invoke(FindDireccionQuery $query): DireccionResponse
    {
        $direccionId = DireccionId::fromInt($query->id);
        $direccion = $this->direccionRepository->findById($direccionId);

        if ($direccion === null) {
            throw DireccionNotFoundException::withId($query->id);
        }

        return DireccionResponse::fromDireccion($direccion);
    }
}
