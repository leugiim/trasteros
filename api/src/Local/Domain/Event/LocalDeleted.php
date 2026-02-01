<?php

declare(strict_types=1);

namespace App\Local\Domain\Event;

final readonly class LocalDeleted
{
    private function __construct(
        public int $localId
    ) {
    }

    public static function create(int $localId): self
    {
        return new self($localId);
    }
}
