<?php

declare(strict_types=1);

namespace App\Users\Application\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UserRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'El nombre es obligatorio')]
        #[Assert\Length(max: 100, maxMessage: 'El nombre no puede superar los 100 caracteres')]
        public string $nombre,

        #[Assert\NotBlank(message: 'El email es obligatorio')]
        #[Assert\Email(message: 'El email no es válido')]
        public string $email,

        #[Assert\Length(min: 6, minMessage: 'La contraseña debe tener al menos 6 caracteres')]
        public ?string $password = null,

        #[Assert\Choice(choices: ['admin', 'gestor', 'readonly'], message: 'El rol no es válido')]
        public string $rol = 'gestor',

        public bool $activo = true
    ) {
    }
}
