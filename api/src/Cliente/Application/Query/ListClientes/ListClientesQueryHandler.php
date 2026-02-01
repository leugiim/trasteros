<?php

declare(strict_types=1);

namespace App\Cliente\Application\Query\ListClientes;

use App\Cliente\Application\DTO\ClienteResponse;
use App\Cliente\Domain\Repository\ClienteRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ListClientesQueryHandler
{
    public function __construct(
        private ClienteRepositoryInterface $clienteRepository
    ) {
    }

    /**
     * @return ClienteResponse[]
     */
    public function __invoke(ListClientesQuery $query): array
    {
        if ($query->search !== null && trim($query->search) !== '') {
            $clientes = $this->clienteRepository->searchByNombreOrApellidos($query->search);
        } elseif ($query->onlyActivos === true) {
            $clientes = $this->clienteRepository->findActivos();
        } else {
            $clientes = $this->clienteRepository->findAll();
        }

        return ClienteResponse::fromClientes($clientes);
    }
}
