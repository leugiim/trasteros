<?php

declare(strict_types=1);

namespace App\Users\Application\Command\CreateUser;

final readonly class CreateUserCommand
{
    public function __construct(
        public string $nombre,
        public string $email,
        public string $password,
        public string $rol = 'gestor',
        public bool $activo = true
    ) {
    }
}
