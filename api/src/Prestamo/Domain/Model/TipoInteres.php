<?php

declare(strict_types=1);

namespace App\Prestamo\Domain\Model;

use App\Prestamo\Domain\Exception\InvalidTipoInteresException;

final readonly class TipoInteres
{
    private function __construct(
        public float $value
    ) {
        if ($value < 0) {
            throw InvalidTipoInteresException::negative($value);
        }

        if ($value > 99.9999) {
            throw InvalidTipoInteresException::tooLarge($value);
        }
    }

    public static function fromFloat(float $value): self
    {
        return new self($value);
    }

    public function equals(TipoInteres $other): bool
    {
        return abs($this->value - $other->value) < 0.0001;
    }

    public function __toString(): string
    {
        return number_format($this->value, 4, '.', '');
    }
}
