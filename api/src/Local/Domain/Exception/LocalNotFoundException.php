<?php

declare(strict_types=1);

namespace App\Local\Domain\Exception;

final class LocalNotFoundException extends \RuntimeException
{
    public static function withId(int $id): self
    {
        return new self(sprintf('Local with id "%d" not found', $id));
    }
}
