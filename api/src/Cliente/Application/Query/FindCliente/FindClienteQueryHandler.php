<?php

declare(strict_types=1);

namespace App\Cliente\Application\Query\FindCliente;

use App\Cliente\Application\DTO\ClienteResponse;
use App\Cliente\Domain\Exception\ClienteNotFoundException;
use App\Cliente\Domain\Model\ClienteId;
use App\Cliente\Domain\Repository\ClienteRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class FindClienteQueryHandler
{
    public function __construct(
        private ClienteRepositoryInterface $clienteRepository
    ) {
    }

    public function __invoke(FindClienteQuery $query): ClienteResponse
    {
        $clienteId = ClienteId::fromInt($query->id);
        $cliente = $this->clienteRepository->findById($clienteId);

        if ($cliente === null) {
            throw ClienteNotFoundException::withId($query->id);
        }

        return ClienteResponse::fromCliente($cliente);
    }
}
