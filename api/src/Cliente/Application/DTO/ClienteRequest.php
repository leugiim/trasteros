<?php

declare(strict_types=1);

namespace App\Cliente\Application\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class ClienteRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'El nombre es obligatorio')]
        #[Assert\Length(max: 100, maxMessage: 'El nombre no puede superar los 100 caracteres')]
        public string $nombre = '',

        #[Assert\NotBlank(message: 'Los apellidos son obligatorios')]
        #[Assert\Length(max: 200, maxMessage: 'Los apellidos no pueden superar los 200 caracteres')]
        public string $apellidos = '',

        #[Assert\Length(max: 20, maxMessage: 'El DNI/NIE no puede superar los 20 caracteres')]
        public ?string $dniNie = null,

        #[Assert\Email(message: 'El email no tiene un formato válido')]
        #[Assert\Length(max: 255, maxMessage: 'El email no puede superar los 255 caracteres')]
        public ?string $email = null,

        #[Assert\Length(max: 20, maxMessage: 'El teléfono no puede superar los 20 caracteres')]
        public ?string $telefono = null,

        public bool $activo = true
    ) {
    }
}
