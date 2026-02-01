<?php

declare(strict_types=1);

namespace App\Ingreso\Domain\Model;

final readonly class IngresoId
{
    private function __construct(
        public int $value
    ) {
    }

    public static function fromInt(int $id): self
    {
        return new self($id);
    }

    public function equals(IngresoId $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
