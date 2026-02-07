<?php

declare(strict_types=1);

namespace App\Trastero\Application\Query\ListTrasteros;

use App\Contrato\Domain\Model\ContratoEstado;
use App\Contrato\Domain\Repository\ContratoRepositoryInterface;
use App\Trastero\Application\DTO\TrasteroResponse;
use App\Trastero\Domain\Model\TrasteroEstado;
use App\Trastero\Domain\Repository\TrasteroRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ListTrasterosQueryHandler
{
    public function __construct(
        private TrasteroRepositoryInterface $trasteroRepository,
        private ContratoRepositoryInterface $contratoRepository
    ) {
    }

    /**
     * @return TrasteroResponse[]
     */
    public function __invoke(ListTrasterosQuery $query): array
    {
        if ($query->localId !== null) {
            $trasteros = $this->trasteroRepository->findByLocalId($query->localId);
        } elseif ($query->onlyActive === true) {
            $trasteros = $this->trasteroRepository->findActiveTrasteros();
        } else {
            $trasteros = $this->trasteroRepository->findAll();
        }

        $responses = array_map(
            fn($trastero) => TrasteroResponse::fromTrasteroWithContratos(
                $trastero,
                $this->contratoRepository->findByTrasteroId($trastero->id()->value)
            ),
            $trasteros
        );

        if ($query->estado !== null) {
            $estadoFiltro = TrasteroEstado::tryFromString($query->estado);
            $responses = array_values(array_filter(
                $responses,
                fn(TrasteroResponse $r) => $r->estado === $estadoFiltro?->value
            ));
        }

        return $responses;
    }
}
