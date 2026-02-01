<?php

declare(strict_types=1);

namespace App\Contrato\Application\DTO;

use App\Contrato\Domain\Model\Contrato;

final readonly class ContratoResponse
{
    public function __construct(
        public int $id,
        public int $trasteroId,
        public string $trasteroNumero,
        public int $clienteId,
        public string $clienteNombre,
        public string $fechaInicio,
        public ?string $fechaFin,
        public float $precioMensual,
        public ?float $fianza,
        public bool $fianzaPagada,
        public string $estado,
        public ?int $duracionMeses,
        public string $createdAt,
        public string $updatedAt
    ) {
    }

    public static function fromContrato(Contrato $contrato): self
    {
        return new self(
            id: $contrato->id()->value,
            trasteroId: $contrato->trastero()->id()->value,
            trasteroNumero: $contrato->trastero()->numero(),
            clienteId: $contrato->cliente()->id()->value,
            clienteNombre: $contrato->cliente()->nombreCompleto(),
            fechaInicio: $contrato->fechaInicio()->format('Y-m-d'),
            fechaFin: $contrato->fechaFin()?->format('Y-m-d'),
            precioMensual: $contrato->precioMensual()->value,
            fianza: $contrato->fianza()?->value,
            fianzaPagada: $contrato->fianzaPagada(),
            estado: $contrato->estado()->value,
            duracionMeses: $contrato->getDuracionMeses(),
            createdAt: $contrato->createdAt()->format('Y-m-d H:i:s'),
            updatedAt: $contrato->updatedAt()->format('Y-m-d H:i:s')
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'trastero' => [
                'id' => $this->trasteroId,
                'numero' => $this->trasteroNumero,
            ],
            'cliente' => [
                'id' => $this->clienteId,
                'nombre' => $this->clienteNombre,
            ],
            'fechaInicio' => $this->fechaInicio,
            'fechaFin' => $this->fechaFin,
            'precioMensual' => $this->precioMensual,
            'fianza' => $this->fianza,
            'fianzaPagada' => $this->fianzaPagada,
            'estado' => $this->estado,
            'duracionMeses' => $this->duracionMeses,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }
}
