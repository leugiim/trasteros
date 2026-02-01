<?php

declare(strict_types=1);

namespace App\Cliente\Domain\Exception;

final class DuplicatedDniNieException extends \DomainException
{
    public static function withDniNie(string $dniNie): self
    {
        return new self(sprintf('Ya existe un cliente con el DNI/NIE %s', $dniNie));
    }
}
