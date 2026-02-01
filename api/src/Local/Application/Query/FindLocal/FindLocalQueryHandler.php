<?php

declare(strict_types=1);

namespace App\Local\Application\Query\FindLocal;

use App\Local\Application\DTO\LocalResponse;
use App\Local\Domain\Exception\LocalNotFoundException;
use App\Local\Domain\Model\LocalId;
use App\Local\Domain\Repository\LocalRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class FindLocalQueryHandler
{
    public function __construct(
        private LocalRepositoryInterface $localRepository
    ) {
    }

    public function __invoke(FindLocalQuery $query): LocalResponse
    {
        $localId = LocalId::fromInt($query->id);
        $local = $this->localRepository->findById($localId);

        if ($local === null) {
            throw LocalNotFoundException::withId($query->id);
        }

        return LocalResponse::fromLocal($local);
    }
}
