<?php

declare(strict_types=1);

namespace App\Trastero\Application\DTO;

use App\Contrato\Domain\Model\Contrato;
use App\Trastero\Domain\Model\Trastero;
use App\Trastero\Domain\Model\TrasteroEstado;

final readonly class TrasteroResponse
{
    public function __construct(
        public int $id,
        public int $localId,
        public string $localNombre,
        public string $numero,
        public ?string $nombre,
        public float $superficie,
        public float $precioMensual,
        public string $estado,
        public string $estadoLabel,
        public string $createdAt,
        public string $updatedAt,
        public ?string $deletedAt
    ) {
    }

    /**
     * @param Contrato[] $contratos Contratos no cancelados del trastero
     */
    public static function fromTrasteroWithContratos(Trastero $trastero, array $contratos): self
    {
        $estado = self::calcularEstado($trastero, $contratos);

        return new self(
            id: $trastero->id()->value,
            localId: $trastero->local()->id()->value,
            localNombre: $trastero->local()->nombre(),
            numero: $trastero->numero(),
            nombre: $trastero->nombre(),
            superficie: $trastero->superficie()->value,
            precioMensual: $trastero->precioMensual()->value,
            estado: $estado->value,
            estadoLabel: $estado->label(),
            createdAt: $trastero->createdAt()->format('Y-m-d H:i:s'),
            updatedAt: $trastero->updatedAt()->format('Y-m-d H:i:s'),
            deletedAt: $trastero->deletedAt()?->format('Y-m-d H:i:s')
        );
    }

    /**
     * @param Contrato[] $contratos
     */
    private static function calcularEstado(Trastero $trastero, array $contratos): TrasteroEstado
    {
        if ($trastero->estado() === TrasteroEstado::MANTENIMIENTO) {
            return TrasteroEstado::MANTENIMIENTO;
        }

        $hoy = new \DateTimeImmutable('today');

        foreach ($contratos as $contrato) {
            $estadoContrato = $contrato->estadoCalculado($hoy);
            if ($estadoContrato->isActivo()) {
                return TrasteroEstado::OCUPADO;
            }
        }

        foreach ($contratos as $contrato) {
            $estadoContrato = $contrato->estadoCalculado($hoy);
            if ($estadoContrato->isPendiente()) {
                return TrasteroEstado::RESERVADO;
            }
        }

        return TrasteroEstado::DISPONIBLE;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'localId' => $this->localId,
            'localNombre' => $this->localNombre,
            'numero' => $this->numero,
            'nombre' => $this->nombre,
            'superficie' => $this->superficie,
            'precioMensual' => $this->precioMensual,
            'estado' => $this->estado,
            'estadoLabel' => $this->estadoLabel,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'deletedAt' => $this->deletedAt,
        ];
    }
}
