<?php

declare(strict_types=1);

namespace App\Gasto\Application\Query\ListGastos;

use App\Gasto\Application\DTO\GastoResponse;
use App\Gasto\Domain\Model\GastoCategoria;
use App\Gasto\Domain\Repository\GastoRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ListGastosQueryHandler
{
    public function __construct(
        private GastoRepositoryInterface $gastoRepository
    ) {
    }

    /**
     * @return GastoResponse[]
     */
    public function __invoke(ListGastosQuery $query): array
    {
        // Aplicar filtros según los parámetros
        if ($query->localId !== null && $query->desde !== null && $query->hasta !== null) {
            $desde = \DateTimeImmutable::createFromFormat('Y-m-d', $query->desde);
            $hasta = \DateTimeImmutable::createFromFormat('Y-m-d', $query->hasta);

            if ($desde === false || $hasta === false) {
                throw new \InvalidArgumentException('Invalid date format. Expected Y-m-d');
            }

            $gastos = $this->gastoRepository->findByLocalAndDateRange($query->localId, $desde, $hasta);
        } elseif ($query->localId !== null && $query->categoria !== null) {
            $categoria = GastoCategoria::fromString($query->categoria);
            $gastos = $this->gastoRepository->findByLocalAndCategoria($query->localId, $categoria);
        } elseif ($query->localId !== null) {
            $gastos = $this->gastoRepository->findByLocalId($query->localId);
        } elseif ($query->categoria !== null) {
            $categoria = GastoCategoria::fromString($query->categoria);
            $gastos = $this->gastoRepository->findByCategoria($categoria);
        } elseif ($query->desde !== null && $query->hasta !== null) {
            $desde = \DateTimeImmutable::createFromFormat('Y-m-d', $query->desde);
            $hasta = \DateTimeImmutable::createFromFormat('Y-m-d', $query->hasta);

            if ($desde === false || $hasta === false) {
                throw new \InvalidArgumentException('Invalid date format. Expected Y-m-d');
            }

            $gastos = $this->gastoRepository->findByDateRange($desde, $hasta);
        } elseif ($query->onlyActive === true) {
            $gastos = $this->gastoRepository->findActiveGastos();
        } else {
            $gastos = $this->gastoRepository->findAll();
        }

        return array_map(
            fn($gasto) => GastoResponse::fromGasto($gasto),
            $gastos
        );
    }
}
