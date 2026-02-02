<?php

declare(strict_types=1);

namespace App\Trastero\Application\Query\FindTrasterosDisponibles;

use App\Trastero\Application\DTO\TrasteroResponse;
use App\Trastero\Domain\Repository\TrasteroRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class FindTrasterosDisponiblesQueryHandler
{
    public function __construct(
        private TrasteroRepositoryInterface $trasteroRepository
    ) {
    }

    /**
     * @return TrasteroResponse[]
     */
    public function __invoke(FindTrasterosDisponiblesQuery $query): array
    {
        $trasteros = $this->trasteroRepository->findDisponibles();

        return array_map(
            fn($trastero) => TrasteroResponse::fromTrastero($trastero),
            $trasteros
        );
    }
}
