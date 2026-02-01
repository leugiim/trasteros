<?php

declare(strict_types=1);

namespace App\Cliente\Domain\Exception;

final class DuplicatedEmailException extends \DomainException
{
    public static function withEmail(string $email): self
    {
        return new self(sprintf('Ya existe un cliente con el email %s', $email));
    }
}
