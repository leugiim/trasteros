<?php

declare(strict_types=1);

namespace App\Users\Application\Query\FindUser;

final readonly class FindUserQuery
{
    public function __construct(
        public string $id
    ) {
    }
}
