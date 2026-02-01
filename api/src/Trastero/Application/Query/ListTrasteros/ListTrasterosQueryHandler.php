<?php

declare(strict_types=1);

namespace App\Trastero\Application\Query\ListTrasteros;

use App\Trastero\Application\DTO\TrasteroResponse;
use App\Trastero\Domain\Exception\InvalidTrasteroEstadoException;
use App\Trastero\Domain\Model\TrasteroEstado;
use App\Trastero\Domain\Repository\TrasteroRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ListTrasterosQueryHandler
{
    public function __construct(
        private TrasteroRepositoryInterface $trasteroRepository
    ) {
    }

    /**
     * @return TrasteroResponse[]
     */
    public function __invoke(ListTrasterosQuery $query): array
    {
        if ($query->estado !== null) {
            $estado = TrasteroEstado::tryFromString($query->estado);
            if ($estado === null) {
                throw InvalidTrasteroEstadoException::invalidValue($query->estado);
            }

            if ($query->localId !== null) {
                $trasteros = $this->trasteroRepository->findByLocalAndEstado($query->localId, $estado);
            } else {
                $trasteros = $this->trasteroRepository->findByEstado($estado);
            }
        } elseif ($query->localId !== null) {
            $trasteros = $this->trasteroRepository->findByLocalId($query->localId);
        } elseif ($query->onlyActive === true) {
            $trasteros = $this->trasteroRepository->findActiveTrasteros();
        } else {
            $trasteros = $this->trasteroRepository->findAll();
        }

        return array_map(
            fn($trastero) => TrasteroResponse::fromTrastero($trastero),
            $trasteros
        );
    }
}
