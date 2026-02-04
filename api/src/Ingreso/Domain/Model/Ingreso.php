<?php

declare(strict_types=1);

namespace App\Ingreso\Domain\Model;

use App\Contrato\Domain\Model\Contrato;
use App\Users\Domain\Model\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'ingreso')]
#[ORM\Index(name: 'idx_ingreso_fecha_pago', columns: ['fecha_pago'])]
#[ORM\Index(name: 'idx_ingreso_categoria', columns: ['categoria'])]
#[ORM\Index(name: 'idx_ingreso_deleted_at', columns: ['deleted_at'])]
#[ORM\HasLifecycleCallbacks]
class Ingreso
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Contrato::class)]
    #[ORM\JoinColumn(name: 'contrato_id', referencedColumnName: 'id', nullable: false)]
    private Contrato $contrato;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $concepto;

    #[ORM\Column(type: Types::DECIMAL, precision: 8, scale: 2)]
    private string $importe;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private \DateTimeImmutable $fechaPago;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true, enumType: MetodoPago::class)]
    private ?MetodoPago $metodoPago = null;

    #[ORM\Column(type: Types::STRING, length: 50, enumType: IngresoCategoria::class)]
    private IngresoCategoria $categoria;

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
        Contrato $contrato,
        string $concepto,
        Importe $importe,
        \DateTimeImmutable $fechaPago,
        IngresoCategoria $categoria,
        ?MetodoPago $metodoPago = null
    ) {
        $this->contrato = $contrato;
        $this->concepto = $concepto;
        $this->importe = (string) $importe->value;
        $this->fechaPago = $fechaPago;
        $this->categoria = $categoria;
        $this->metodoPago = $metodoPago;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public static function create(
        Contrato $contrato,
        string $concepto,
        Importe $importe,
        \DateTimeImmutable $fechaPago,
        IngresoCategoria $categoria,
        ?MetodoPago $metodoPago = null
    ): self {
        return new self(
            contrato: $contrato,
            concepto: $concepto,
            importe: $importe,
            fechaPago: $fechaPago,
            categoria: $categoria,
            metodoPago: $metodoPago
        );
    }

    public function update(
        Contrato $contrato,
        string $concepto,
        Importe $importe,
        \DateTimeImmutable $fechaPago,
        IngresoCategoria $categoria,
        ?MetodoPago $metodoPago
    ): void {
        $this->contrato = $contrato;
        $this->concepto = $concepto;
        $this->importe = (string) $importe->value;
        $this->fechaPago = $fechaPago;
        $this->categoria = $categoria;
        $this->metodoPago = $metodoPago;
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

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function id(): ?IngresoId
    {
        return $this->id !== null ? IngresoId::fromInt($this->id) : null;
    }

    public function contrato(): Contrato
    {
        return $this->contrato;
    }

    public function concepto(): string
    {
        return $this->concepto;
    }

    public function importe(): Importe
    {
        return Importe::fromFloat((float) $this->importe);
    }

    public function fechaPago(): \DateTimeImmutable
    {
        return $this->fechaPago;
    }

    public function categoria(): IngresoCategoria
    {
        return $this->categoria;
    }

    public function metodoPago(): ?MetodoPago
    {
        return $this->metodoPago;
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
