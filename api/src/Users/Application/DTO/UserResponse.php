<?php

declare(strict_types=1);

namespace App\Users\Application\DTO;

use App\Users\Domain\Model\User;

final readonly class UserResponse
{
    public function __construct(
        public string $id,
        public string $nombre,
        public string $email,
        public string $rol,
        public bool $activo,
        public string $createdAt,
        public string $updatedAt
    ) {
    }

    public static function fromUser(User $user): self
    {
        return new self(
            id: $user->id()->value,
            nombre: $user->nombre(),
            email: $user->email()->value,
            rol: $user->rol()->value,
            activo: $user->isActivo(),
            createdAt: $user->createdAt()->format(\DateTimeInterface::ATOM),
            updatedAt: $user->updatedAt()->format(\DateTimeInterface::ATOM)
        );
    }

    /**
     * @param User[] $users
     * @return self[]
     */
    public static function fromUsers(array $users): array
    {
        return array_map(fn(User $user) => self::fromUser($user), $users);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'email' => $this->email,
            'rol' => $this->rol,
            'activo' => $this->activo,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }
}
