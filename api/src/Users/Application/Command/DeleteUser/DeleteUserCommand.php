<?php

declare(strict_types=1);

namespace App\Users\Application\Command\DeleteUser;

final readonly class DeleteUserCommand
{
    public function __construct(
        public string $id
    ) {
    }
}
