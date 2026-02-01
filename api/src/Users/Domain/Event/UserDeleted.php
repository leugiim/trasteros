<?php

declare(strict_types=1);

namespace App\Users\Domain\Event;

final readonly class UserDeleted
{
    public function __construct(
        public string $userId,
        public string $email,
        public \DateTimeImmutable $occurredOn
    ) {
    }

    public static function create(string $userId, string $email): self
    {
        return new self($userId, $email, new \DateTimeImmutable());
    }
}
