<?php

declare(strict_types=1);

namespace App\Contrato\Domain\Exception;

use App\Shared\Domain\Exception\NotFoundError;

final class ContratoNotFoundException extends NotFoundError
{
    public static function withId(int $id): self
    {
        return new self(sprintf('Contrato with id %d not found', $id));
    }

    public function errorCode(): string
    {
        return 'CONTRATO_NOT_FOUND';
    }
}
