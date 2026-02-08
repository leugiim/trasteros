<?php

declare(strict_types=1);

namespace App\Auth\Application\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class RefreshTokenRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'El refresh token es obligatorio')]
        public string $refreshToken
    ) {
    }
}
