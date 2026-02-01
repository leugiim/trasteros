<?php

declare(strict_types=1);

namespace App\Cliente\Domain\Model;

use App\Cliente\Domain\Exception\InvalidDniNieException;

final readonly class DniNie
{
    private const DNI_NIE_PATTERN = '/^[0-9XYZ][0-9]{7}[A-Z]$/i';
    private const NIE_LETTERS = ['X', 'Y', 'Z'];
    private const DNI_LETTERS = 'TRWAGMYFPDXBNJZSQVHLCKE';

    private function __construct(
        public string $value
    ) {
    }

    public static function fromString(string $dniNie): self
    {
        $cleaned = strtoupper(trim($dniNie));

        if (empty($cleaned)) {
            throw InvalidDniNieException::empty();
        }

        if (!preg_match(self::DNI_NIE_PATTERN, $cleaned)) {
            throw InvalidDniNieException::invalidFormat($dniNie);
        }

        if (!self::isValidCheckLetter($cleaned)) {
            throw InvalidDniNieException::invalidCheckLetter($dniNie);
        }

        return new self($cleaned);
    }

    private static function isValidCheckLetter(string $dniNie): bool
    {
        $number = substr($dniNie, 0, 8);
        $letter = substr($dniNie, 8, 1);

        if (in_array($number[0], self::NIE_LETTERS, true)) {
            $replacements = ['X' => '0', 'Y' => '1', 'Z' => '2'];
            $number = strtr($number, $replacements);
        }

        if (!is_numeric($number)) {
            return false;
        }

        $expectedLetter = self::DNI_LETTERS[(int) $number % 23];

        return $letter === $expectedLetter;
    }

    public function equals(DniNie $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
