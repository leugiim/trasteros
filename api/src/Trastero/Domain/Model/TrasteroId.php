<?php

declare(strict_types=1);

namespace App\Trastero\Domain\Model;

final readonly class TrasteroId
{
    private function __construct(
        public int $value
    ) {
    }

    public static function fromInt(int $id): self
    {
        return new self($id);
    }

    public function equals(TrasteroId $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
