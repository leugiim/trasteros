<?php

declare(strict_types=1);

namespace App\Auth\Application\Command\RefreshToken;

final readonly class RefreshTokenCommand
{
    public function __construct(
        public string $refreshToken
    ) {
    }
}
