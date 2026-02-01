<?php

declare(strict_types=1);

namespace App\Local\Application\Query\ListLocales;

use App\Local\Application\DTO\LocalResponse;
use App\Local\Domain\Repository\LocalRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ListLocalesQueryHandler
{
    public function __construct(
        private LocalRepositoryInterface $localRepository
    ) {
    }

    /**
     * @return LocalResponse[]
     */
    public function __invoke(ListLocalesQuery $query): array
    {
        if ($query->onlyActive === true) {
            $locales = $this->localRepository->findActiveLocales();
        } elseif ($query->nombre !== null) {
            $locales = $this->localRepository->findByNombre($query->nombre);
        } elseif ($query->direccionId !== null) {
            $locales = $this->localRepository->findByDireccionId($query->direccionId);
        } else {
            $locales = $this->localRepository->findAll();
        }

        return LocalResponse::fromLocales($locales);
    }
}
