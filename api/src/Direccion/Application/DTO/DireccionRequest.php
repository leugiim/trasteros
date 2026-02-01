<?php

declare(strict_types=1);

namespace App\Direccion\Application\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class DireccionRequest
{
    public function __construct(
        #[Assert\Length(max: 50, maxMessage: 'El tipo de vía no puede superar los 50 caracteres')]
        public ?string $tipoVia = null,

        #[Assert\NotBlank(message: 'El nombre de la vía es obligatorio')]
        #[Assert\Length(max: 255, maxMessage: 'El nombre de la vía no puede superar los 255 caracteres')]
        public string $nombreVia = '',

        #[Assert\Length(max: 10, maxMessage: 'El número no puede superar los 10 caracteres')]
        public ?string $numero = null,

        #[Assert\Length(max: 10, maxMessage: 'El piso no puede superar los 10 caracteres')]
        public ?string $piso = null,

        #[Assert\Length(max: 10, maxMessage: 'La puerta no puede superar los 10 caracteres')]
        public ?string $puerta = null,

        #[Assert\NotBlank(message: 'El código postal es obligatorio')]
        #[Assert\Length(max: 10, maxMessage: 'El código postal no puede superar los 10 caracteres')]
        public string $codigoPostal = '',

        #[Assert\NotBlank(message: 'La ciudad es obligatoria')]
        #[Assert\Length(max: 100, maxMessage: 'La ciudad no puede superar los 100 caracteres')]
        public string $ciudad = '',

        #[Assert\NotBlank(message: 'La provincia es obligatoria')]
        #[Assert\Length(max: 100, maxMessage: 'La provincia no puede superar los 100 caracteres')]
        public string $provincia = '',

        #[Assert\Length(max: 100, maxMessage: 'El país no puede superar los 100 caracteres')]
        public string $pais = 'España',

        #[Assert\Range(min: -90, max: 90, notInRangeMessage: 'La latitud debe estar entre -90 y 90 grados')]
        public ?float $latitud = null,

        #[Assert\Range(min: -180, max: 180, notInRangeMessage: 'La longitud debe estar entre -180 y 180 grados')]
        public ?float $longitud = null
    ) {
    }
}
