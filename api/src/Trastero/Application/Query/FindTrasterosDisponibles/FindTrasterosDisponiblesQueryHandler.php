<?php

declare(strict_types=1);

namespace App\Trastero\Application\Query\FindTrasterosDisponibles;

use App\Contrato\Domain\Repository\ContratoRepositoryInterface;
use App\Trastero\Application\DTO\TrasteroResponse;
use App\Trastero\Domain\Model\TrasteroEstado;
use App\Trastero\Domain\Repository\TrasteroRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class FindTrasterosDisponiblesQueryHandler
{
    public function __construct(
        private TrasteroRepositoryInterface $trasteroRepository,
        private ContratoRepositoryInterface $contratoRepository
    ) {
    }

    /**
     * @return TrasteroResponse[]
     */
    public function __invoke(FindTrasterosDisponiblesQuery $query): array
    {
        $trasteros = $this->trasteroRepository->findActiveTrasteros();

        $responses = [];
        foreach ($trasteros as $trastero) {
            $contratos = $this->contratoRepository->findByTrasteroId($trastero->id()->value);
            $response = TrasteroResponse::fromTrasteroWithContratos($trastero, $contratos);

            if ($response->estado === TrasteroEstado::DISPONIBLE->value) {
                $responses[] = $response;
            }
        }

        return $responses;
    }
}
