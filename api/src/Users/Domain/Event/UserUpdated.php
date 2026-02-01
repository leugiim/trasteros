<?php

declare(strict_types=1);

namespace App\Users\Domain\Event;

final readonly class UserUpdated
{
    public function __construct(
        public string $userId,
        public string $email,
        public string $nombre,
        public string $rol,
        public bool $activo,
        public \DateTimeImmutable $occurredOn
    ) {
    }

    public static function create(
        string $userId,
        string $email,
        string $nombre,
        string $rol,
        bool $activo
    ): self {
        return new self($userId, $email, $nombre, $rol, $activo, new \DateTimeImmutable());
    }
}
