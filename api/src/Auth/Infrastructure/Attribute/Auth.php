<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final class Auth
{
    /**
     * @param string[] $roles Roles requeridos (vacío = cualquier usuario autenticado)
     */
    public function __construct(
        public array $roles = []
    ) {
    }
}
