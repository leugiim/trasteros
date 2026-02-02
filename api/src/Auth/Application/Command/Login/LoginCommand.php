<?php

declare(strict_types=1);

namespace App\Auth\Application\Command\Login;

final readonly class LoginCommand
{
    public function __construct(
        public string $email,
        public string $password
    ) {
    }
}
