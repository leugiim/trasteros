<?php

declare(strict_types=1);

namespace App\Auth\Application\DTO;

use App\Users\Domain\Model\User;

final readonly class LoginResponse
{
    public function __construct(
        public string $token,
        public string $refreshToken,
        public string $userId,
        public string $email,
        public string $nombre,
        public string $rol
    ) {
    }

    public static function create(string $token, string $refreshToken, User $user): self
    {
        return new self(
            token: $token,
            refreshToken: $refreshToken,
            userId: $user->id()->value,
            email: $user->email()->value,
            nombre: $user->nombre(),
            rol: $user->rol()->value
        );
    }

    public function toArray(): array
    {
        return [
            'token' => $this->token,
            'refreshToken' => $this->refreshToken,
            'user' => [
                'id' => $this->userId,
                'email' => $this->email,
                'nombre' => $this->nombre,
                'rol' => $this->rol,
            ],
        ];
    }
}
