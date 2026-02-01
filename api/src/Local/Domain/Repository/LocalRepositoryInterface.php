<?php

declare(strict_types=1);

namespace App\Local\Domain\Repository;

use App\Local\Domain\Model\Local;
use App\Local\Domain\Model\LocalId;

interface LocalRepositoryInterface
{
    public function save(Local $local): void;

    public function remove(Local $local): void;

    public function findById(LocalId $id): ?Local;

    /**
     * @return Local[]
     */
    public function findAll(): array;

    /**
     * @return Local[]
     */
    public function findActiveLocales(): array;

    /**
     * @return Local[]
     */
    public function findByNombre(string $nombre): array;

    /**
     * @return Local[]
     */
    public function findByDireccionId(int $direccionId): array;

    public function count(): int;
}
