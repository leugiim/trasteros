<?php

declare(strict_types=1);

namespace App\Direccion\Application\Query\ListDirecciones;

use App\Direccion\Application\DTO\DireccionResponse;
use App\Direccion\Domain\Repository\DireccionRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ListDireccionesQueryHandler
{
    public function __construct(
        private DireccionRepositoryInterface $direccionRepository
    ) {
    }

    /**
     * @return DireccionResponse[]
     */
    public function __invoke(ListDireccionesQuery $query): array
    {
        if ($query->onlyActive === true) {
            $direcciones = $this->direccionRepository->findActiveDirecciones();
        } elseif ($query->ciudad !== null) {
            $direcciones = $this->direccionRepository->findByCiudad($query->ciudad);
        } elseif ($query->provincia !== null) {
            $direcciones = $this->direccionRepository->findByProvincia($query->provincia);
        } elseif ($query->codigoPostal !== null) {
            $direcciones = $this->direccionRepository->findByCodigoPostal($query->codigoPostal);
        } else {
            $direcciones = $this->direccionRepository->findAll();
        }

        return DireccionResponse::fromDirecciones($direcciones);
    }
}
