<?php

declare(strict_types=1);

namespace App\Users\Application\Query\ListUsers;

final readonly class ListUsersQuery
{
    public function __construct(
        public ?bool $activo = null
    ) {
    }
}
