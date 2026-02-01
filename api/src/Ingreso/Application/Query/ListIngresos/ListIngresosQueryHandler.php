<?php

declare(strict_types=1);

namespace App\Ingreso\Application\Query\ListIngresos;

use App\Ingreso\Application\DTO\IngresoResponse;
use App\Ingreso\Domain\Exception\InvalidIngresoCategoriaException;
use App\Ingreso\Domain\Model\IngresoCategoria;
use App\Ingreso\Domain\Repository\IngresoRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ListIngresosQueryHandler
{
    public function __construct(
        private IngresoRepositoryInterface $ingresoRepository
    ) {
    }

    /**
     * @return IngresoResponse[]
     */
    public function __invoke(ListIngresosQuery $query): array
    {
        if ($query->onlyActive === true) {
            $ingresos = $this->ingresoRepository->findActiveIngresos();
        } elseif ($query->contratoId !== null && $query->categoria !== null) {
            $categoria = $this->parseCategoria($query->categoria);
            $ingresos = $this->ingresoRepository->findByContratoAndCategoria($query->contratoId, $categoria);
        } elseif ($query->contratoId !== null && $query->desde !== null && $query->hasta !== null) {
            $desde = $this->parseDate($query->desde);
            $hasta = $this->parseDate($query->hasta);
            $ingresos = $this->ingresoRepository->findByContratoAndDateRange($query->contratoId, $desde, $hasta);
        } elseif ($query->contratoId !== null) {
            $ingresos = $this->ingresoRepository->findByContratoId($query->contratoId);
        } elseif ($query->categoria !== null) {
            $categoria = $this->parseCategoria($query->categoria);
            $ingresos = $this->ingresoRepository->findByCategoria($categoria);
        } elseif ($query->desde !== null && $query->hasta !== null) {
            $desde = $this->parseDate($query->desde);
            $hasta = $this->parseDate($query->hasta);
            $ingresos = $this->ingresoRepository->findByDateRange($desde, $hasta);
        } else {
            $ingresos = $this->ingresoRepository->findAll();
        }

        return array_map(
            fn($ingreso) => IngresoResponse::fromIngreso($ingreso),
            $ingresos
        );
    }

    private function parseCategoria(string $categoria): IngresoCategoria
    {
        try {
            return IngresoCategoria::fromString($categoria);
        } catch (\ValueError $e) {
            throw InvalidIngresoCategoriaException::withValue($categoria);
        }
    }

    private function parseDate(string $date): \DateTimeImmutable
    {
        $parsed = \DateTimeImmutable::createFromFormat('Y-m-d', $date);
        if ($parsed === false) {
            throw new \InvalidArgumentException(sprintf('Invalid date format: %s. Expected Y-m-d', $date));
        }

        return $parsed;
    }
}
