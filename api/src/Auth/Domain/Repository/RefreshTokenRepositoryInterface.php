<?php

declare(strict_types=1);

namespace App\Auth\Domain\Repository;

use App\Auth\Domain\Model\RefreshToken;

interface RefreshTokenRepositoryInterface
{
    public function save(RefreshToken $refreshToken): void;

    public function findByToken(string $token): ?RefreshToken;

    public function deleteByUserId(string $userId): void;

    public function deleteExpired(): void;
}
