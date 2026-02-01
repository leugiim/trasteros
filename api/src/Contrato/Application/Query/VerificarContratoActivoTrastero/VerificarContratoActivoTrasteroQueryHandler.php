<?php

declare(strict_types=1);

namespace App\Contrato\Application\Query\VerificarContratoActivoTrastero;

use App\Contrato\Domain\Repository\ContratoRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class VerificarContratoActivoTrasteroQueryHandler
{
    public function __construct(
        private ContratoRepositoryInterface $contratoRepository
    ) {
    }

    public function __invoke(VerificarContratoActivoTrasteroQuery $query): bool
    {
        return $this->contratoRepository->hasContratoActivoTrastero($query->trasteroId);
    }
}
