<?php

declare(strict_types=1);

namespace App\Gasto\Domain\Model;

use App\Local\Domain\Model\Local;
use App\Users\Domain\Model\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'gasto')]
#[ORM\HasLifecycleCallbacks]
class Gasto
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Local::class)]
    #[ORM\JoinColumn(name: 'local_id', referencedColumnName: 'id', nullable: false)]
    private Local $local;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $concepto;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $descripcion = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $importe;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private \DateTimeImmutable $fecha;

    #[ORM\Column(type: Types::STRING, length: 50, enumType: GastoCategoria::class)]
    private GastoCategoria $categoria;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true, enumType: MetodoPago::class)]
    private ?MetodoPago $metodoPago = null;

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
        Local $local,
        string $concepto,
        Importe $importe,
        \DateTimeImmutable $fecha,
        GastoCategoria $categoria,
        ?string $descripcion = null,
        ?MetodoPago $metodoPago = null
    ) {
        $this->local = $local;
        $this->concepto = $concepto;
        $this->importe = (string) $importe->value;
        $this->fecha = $fecha;
        $this->categoria = $categoria;
        $this->descripcion = $descripcion;
        $this->metodoPago = $metodoPago;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public static function create(
        Local $local,
        string $concepto,
        Importe $importe,
        \DateTimeImmutable $fecha,
        GastoCategoria $categoria,
        ?string $descripcion = null,
        ?MetodoPago $metodoPago = null
    ): self {
        return new self(
            local: $local,
            concepto: $concepto,
            importe: $importe,
            fecha: $fecha,
            categoria: $categoria,
            descripcion: $descripcion,
            metodoPago: $metodoPago
        );
    }

    public function update(
        Local $local,
        string $concepto,
        Importe $importe,
        \DateTimeImmutable $fecha,
        GastoCategoria $categoria,
        ?string $descripcion,
        ?MetodoPago $metodoPago
    ): void {
        $this->local = $local;
        $this->concepto = $concepto;
        $this->importe = (string) $importe->value;
        $this->fecha = $fecha;
        $this->categoria = $categoria;
        $this->descripcion = $descripcion;
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

    public function id(): ?GastoId
    {
        return $this->id !== null ? GastoId::fromInt($this->id) : null;
    }

    public function local(): Local
    {
        return $this->local;
    }

    public function concepto(): string
    {
        return $this->concepto;
    }

    public function descripcion(): ?string
    {
        return $this->descripcion;
    }

    public function importe(): Importe
    {
        return Importe::fromFloat((float) $this->importe);
    }

    public function fecha(): \DateTimeImmutable
    {
        return $this->fecha;
    }

    public function categoria(): GastoCategoria
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
