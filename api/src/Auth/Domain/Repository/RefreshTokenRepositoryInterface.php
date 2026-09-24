<?php

declare(strict_types=1);

namespace App\Auth\Domain\Repository;

use App\Auth\Domain\Model\RefreshToken;

interface RefreshTokenRepositoryInterface
{
    public function save(RefreshToken $refreshToken): void;

    /** Looks the token up by its hash */
    public function findByToken(string $plainToken): ?RefreshToken;

    public function remove(RefreshToken $refreshToken): void;

    public function deleteByUserId(string $userId): void;

    public function deleteExpired(): void;
}
