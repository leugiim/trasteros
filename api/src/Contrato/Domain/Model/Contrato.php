<?php

declare(strict_types=1);

namespace App\Contrato\Domain\Model;

use App\Cliente\Domain\Model\Cliente;
use App\Contrato\Domain\Exception\InvalidContratoDateException;
use App\Trastero\Domain\Model\Trastero;
use App\Users\Domain\Model\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'contrato')]
#[ORM\Index(name: 'idx_contrato_estado', columns: ['estado'])]
#[ORM\Index(name: 'idx_contrato_fecha_inicio', columns: ['fecha_inicio'])]
#[ORM\Index(name: 'idx_contrato_fecha_fin', columns: ['fecha_fin'])]
#[ORM\Index(name: 'idx_contrato_deleted_at', columns: ['deleted_at'])]
#[ORM\HasLifecycleCallbacks]
class Contrato
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Trastero::class)]
    #[ORM\JoinColumn(name: 'trastero_id', referencedColumnName: 'id', nullable: false)]
    private Trastero $trastero;

    #[ORM\ManyToOne(targetEntity: Cliente::class)]
    #[ORM\JoinColumn(name: 'cliente_id', referencedColumnName: 'id', nullable: false)]
    private Cliente $cliente;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private \DateTimeImmutable $fechaInicio;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $fechaFin = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 8, scale: 2)]
    private string $precioMensual;

    #[ORM\Column(type: Types::DECIMAL, precision: 8, scale: 2, nullable: true)]
    private ?string $fianza = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $fianzaPagada = false;

    #[ORM\Column(type: Types::STRING, length: 50, enumType: ContratoEstado::class)]
    private ContratoEstado $estado;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id', nullable: true)]
    private ?User $createdBy = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'updated_by', referencedColumnName: 'id', nullable: true)]
    private ?User $updatedBy = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $deletedAt = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'deleted_by', referencedColumnName: 'id', nullable: true)]
    private ?User $deletedBy = null;

    private function __construct(
        Trastero $trastero,
        Cliente $cliente,
        \DateTimeImmutable $fechaInicio,
        PrecioMensual $precioMensual,
        ?\DateTimeImmutable $fechaFin = null,
        ?Fianza $fianza = null,
        bool $fianzaPagada = false,
        ?ContratoEstado $estado = null
    ) {
        $this->validateDates($fechaInicio, $fechaFin);

        $this->trastero = $trastero;
        $this->cliente = $cliente;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
        $this->precioMensual = (string) $precioMensual->value;
        $this->fianza = $fianza !== null ? (string) $fianza->value : null;
        $this->fianzaPagada = $fianzaPagada;
        $this->estado = $estado ?? ContratoEstado::ACTIVO;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public static function create(
        Trastero $trastero,
        Cliente $cliente,
        \DateTimeImmutable $fechaInicio,
        PrecioMensual $precioMensual,
        ?\DateTimeImmutable $fechaFin = null,
        ?Fianza $fianza = null,
        bool $fianzaPagada = false,
        ?ContratoEstado $estado = null
    ): self {
        return new self(
            trastero: $trastero,
            cliente: $cliente,
            fechaInicio: $fechaInicio,
            precioMensual: $precioMensual,
            fechaFin: $fechaFin,
            fianza: $fianza,
            fianzaPagada: $fianzaPagada,
            estado: $estado
        );
    }

    public function update(
        Trastero $trastero,
        Cliente $cliente,
        \DateTimeImmutable $fechaInicio,
        PrecioMensual $precioMensual,
        ?\DateTimeImmutable $fechaFin,
        ?Fianza $fianza,
        bool $fianzaPagada,
        ContratoEstado $estado
    ): void {
        $this->validateDates($fechaInicio, $fechaFin);

        $this->trastero = $trastero;
        $this->cliente = $cliente;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
        $this->precioMensual = (string) $precioMensual->value;
        $this->fianza = $fianza !== null ? (string) $fianza->value : null;
        $this->fianzaPagada = $fianzaPagada;
        $this->estado = $estado;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function marcarFianzaPagada(): void
    {
        $this->fianzaPagada = true;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function estadoCalculado(?\DateTimeImmutable $fecha = null): ContratoEstado
    {
        if ($this->estado === ContratoEstado::CANCELADO) {
            return ContratoEstado::CANCELADO;
        }

        $hoy = $fecha ?? new \DateTimeImmutable('today');

        if ($hoy < $this->fechaInicio) {
            return ContratoEstado::PENDIENTE;
        }

        if ($this->fechaFin !== null && $hoy > $this->fechaFin) {
            return ContratoEstado::FINALIZADO;
        }

        return ContratoEstado::ACTIVO;
    }

    public function finalizarAnticipadamente(): void
    {
        $this->fechaFin = new \DateTimeImmutable('today');
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function cancelar(): void
    {
        $this->estado = ContratoEstado::CANCELADO;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function softDelete(User $deletedBy): void
    {
        $this->deletedAt = new \DateTimeImmutable();
        $this->deletedBy = $deletedBy;
    }

    public function restore(): void
    {
        $this->deletedAt = null;
        $this->deletedBy = null;
    }

    public function isActivo(): bool
    {
        return $this->estadoCalculado()->isActivo();
    }

    public function getDuracionMeses(): ?int
    {
        if ($this->fechaFin === null) {
            return null;
        }

        $interval = $this->fechaInicio->diff($this->fechaFin);
        return ($interval->y * 12) + $interval->m;
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    private function validateDates(\DateTimeImmutable $fechaInicio, ?\DateTimeImmutable $fechaFin): void
    {
        if ($fechaFin !== null && $fechaFin < $fechaInicio) {
            throw new InvalidContratoDateException('La fecha de fin debe ser posterior a la fecha de inicio');
        }
    }

    public function id(): ?ContratoId
    {
        return $this->id !== null ? ContratoId::fromInt($this->id) : null;
    }

    public function trastero(): Trastero
    {
        return $this->trastero;
    }

    public function cliente(): Cliente
    {
        return $this->cliente;
    }

    public function fechaInicio(): \DateTimeImmutable
    {
        return $this->fechaInicio;
    }

    public function fechaFin(): ?\DateTimeImmutable
    {
        return $this->fechaFin;
    }

    public function precioMensual(): PrecioMensual
    {
        return PrecioMensual::fromFloat((float) $this->precioMensual);
    }

    public function fianza(): ?Fianza
    {
        return $this->fianza !== null ? Fianza::fromFloat((float) $this->fianza) : null;
    }

    public function fianzaPagada(): bool
    {
        return $this->fianzaPagada;
    }

    public function estado(): ContratoEstado
    {
        return $this->estado;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function deletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }

    public function createdBy(): ?User
    {
        return $this->createdBy;
    }

    public function updatedBy(): ?User
    {
        return $this->updatedBy;
    }

    public function deletedBy(): ?User
    {
        return $this->deletedBy;
    }
}
