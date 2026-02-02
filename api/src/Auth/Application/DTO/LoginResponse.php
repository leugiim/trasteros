<?php

declare(strict_types=1);

namespace App\Auth\Application\DTO;

use App\Users\Domain\Model\User;

final readonly class LoginResponse
{
    public function __construct(
        public string $token,
        public string $userId,
        public string $email,
        public string $nombre,
        public string $rol
    ) {
    }

    public static function create(string $token, User $user): self
    {
        return new self(
            token: $token,
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
            'user' => [
                'id' => $this->userId,
                'email' => $this->email,
                'nombre' => $this->nombre,
                'rol' => $this->rol,
            ],
        ];
    }
}
