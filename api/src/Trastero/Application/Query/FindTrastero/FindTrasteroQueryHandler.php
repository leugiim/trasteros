<?php

declare(strict_types=1);

namespace App\Trastero\Application\Query\FindTrastero;

use App\Contrato\Domain\Repository\ContratoRepositoryInterface;
use App\Trastero\Application\DTO\TrasteroResponse;
use App\Trastero\Domain\Exception\TrasteroNotFoundException;
use App\Trastero\Domain\Model\TrasteroId;
use App\Trastero\Domain\Repository\TrasteroRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class FindTrasteroQueryHandler
{
    public function __construct(
        private TrasteroRepositoryInterface $trasteroRepository,
        private ContratoRepositoryInterface $contratoRepository
    ) {
    }

    public function __invoke(FindTrasteroQuery $query): TrasteroResponse
    {
        $trastero = $this->trasteroRepository->findById(TrasteroId::fromInt($query->id));
        if ($trastero === null) {
            throw TrasteroNotFoundException::withId($query->id);
        }

        $contratos = $this->contratoRepository->findByTrasteroId($query->id);

        return TrasteroResponse::fromTrasteroWithContratos($trastero, $contratos);
    }
}
