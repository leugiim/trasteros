<?php

declare(strict_types=1);

namespace App\Cliente\Domain\Model;

final readonly class ClienteId
{
    private function __construct(
        public int $value
    ) {
    }

    public static function fromInt(int $id): self
    {
        return new self($id);
    }

    public function equals(ClienteId $other): bool
    {
        return $this->value === $other->value;
    }
}
