<?php

declare(strict_types=1);

namespace App\Direccion\Domain\Model;

use App\Direccion\Domain\Exception\InvalidCodigoPostalException;

final readonly class CodigoPostal
{
    private function __construct(
        public string $value
    ) {
    }

    public static function fromString(string $codigoPostal): self
    {
        $cleaned = trim($codigoPostal);

        if (empty($cleaned)) {
            throw InvalidCodigoPostalException::empty();
        }

        if (strlen($cleaned) > 10) {
            throw InvalidCodigoPostalException::tooLong($cleaned);
        }

        return new self($cleaned);
    }

    public function equals(CodigoPostal $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
