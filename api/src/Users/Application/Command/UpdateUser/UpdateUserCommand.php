<?php

declare(strict_types=1);

namespace App\Users\Application\Command\UpdateUser;

final readonly class UpdateUserCommand
{
    public function __construct(
        public string $id,
        public string $nombre,
        public string $email,
        public string $rol,
        public bool $activo,
        public ?string $password = null
    ) {
    }
}
