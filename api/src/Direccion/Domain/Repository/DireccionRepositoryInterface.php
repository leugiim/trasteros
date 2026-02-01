<?php

declare(strict_types=1);

namespace App\Direccion\Domain\Repository;

use App\Direccion\Domain\Model\Direccion;
use App\Direccion\Domain\Model\DireccionId;

interface DireccionRepositoryInterface
{
    public function save(Direccion $direccion): void;

    public function remove(Direccion $direccion): void;

    public function findById(DireccionId $id): ?Direccion;

    /**
     * @return Direccion[]
     */
    public function findAll(): array;

    /**
     * @return Direccion[]
     */
    public function findActiveDirecciones(): array;

    /**
     * @return Direccion[]
     */
    public function findByCiudad(string $ciudad): array;

    /**
     * @return Direccion[]
     */
    public function findByProvincia(string $provincia): array;

    /**
     * @return Direccion[]
     */
    public function findByCodigoPostal(string $codigoPostal): array;

    public function count(): int;
}
