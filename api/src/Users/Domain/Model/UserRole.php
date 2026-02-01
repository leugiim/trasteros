<?php

declare(strict_types=1);

namespace App\Users\Domain\Model;

enum UserRole: string
{
    case ADMIN = 'admin';
    case GESTOR = 'gestor';
    case READONLY = 'readonly';

    public static function fromString(string $role): self
    {
        return self::from($role);
    }

    public function toSymfonyRole(): string
    {
        return 'ROLE_' . strtoupper($this->value);
    }
}
