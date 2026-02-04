<?php

declare(strict_types=1);

namespace App\Prestamo\Domain\Model;

use App\Local\Domain\Model\Local;
use App\Users\Domain\Model\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'prestamo')]
#[ORM\Index(name: 'idx_prestamo_estado', columns: ['estado'])]
#[ORM\Index(name: 'idx_prestamo_fecha_concesion', columns: ['fecha_concesion'])]
#[ORM\Index(name: 'idx_prestamo_entidad_bancaria', columns: ['entidad_bancaria'])]
#[ORM\Index(name: 'idx_prestamo_deleted_at', columns: ['deleted_at'])]
#[ORM\HasLifecycleCallbacks]
class Prestamo
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Local::class)]
    #[ORM\JoinColumn(name: 'local_id', referencedColumnName: 'id', nullable: false)]
    private Local $local;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $entidadBancaria = null;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true)]
    private ?string $numeroPrestamo = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
    private string $capitalSolicitado;

    #[ORM\Column(name: 'total_a_devolver', type: Types::DECIMAL, precision: 12, scale: 2)]
    private string $totalADevolver;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 4, nullable: true)]
    private ?string $tipoInteres = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private \DateTimeImmutable $fechaConcesion;

    #[ORM\Column(type: Types::STRING, length: 50, enumType: PrestamoEstado::class)]
    private PrestamoEstado $estado;

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
        CapitalSolicitado $capitalSolicitado,
        TotalADevolver $totalADevolver,
        \DateTimeImmutable $fechaConcesion,
        ?string $entidadBancaria = null,
        ?string $numeroPrestamo = null,
        ?TipoInteres $tipoInteres = null,
        ?PrestamoEstado $estado = null
    ) {
        $this->local = $local;
        $this->capitalSolicitado = (string) $capitalSolicitado->value;
        $this->totalADevolver = (string) $totalADevolver->value;
        $this->fechaConcesion = $fechaConcesion;
        $this->entidadBancaria = $entidadBancaria;
        $this->numeroPrestamo = $numeroPrestamo;
        $this->tipoInteres = $tipoInteres !== null ? (string) $tipoInteres->value : null;
        $this->estado = $estado ?? PrestamoEstado::ACTIVO;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public static function create(
        Local $local,
        CapitalSolicitado $capitalSolicitado,
        TotalADevolver $totalADevolver,
        \DateTimeImmutable $fechaConcesion,
        ?string $entidadBancaria = null,
        ?string $numeroPrestamo = null,
        ?TipoInteres $tipoInteres = null,
        ?PrestamoEstado $estado = null
    ): self {
        return new self(
            local: $local,
            capitalSolicitado: $capitalSolicitado,
            totalADevolver: $totalADevolver,
            fechaConcesion: $fechaConcesion,
            entidadBancaria: $entidadBancaria,
            numeroPrestamo: $numeroPrestamo,
            tipoInteres: $tipoInteres,
            estado: $estado
        );
    }

    public function update(
        Local $local,
        CapitalSolicitado $capitalSolicitado,
        TotalADevolver $totalADevolver,
        \DateTimeImmutable $fechaConcesion,
        ?string $entidadBancaria,
        ?string $numeroPrestamo,
        ?TipoInteres $tipoInteres,
        PrestamoEstado $estado
    ): void {
        $this->local = $local;
        $this->capitalSolicitado = (string) $capitalSolicitado->value;
        $this->totalADevolver = (string) $totalADevolver->value;
        $this->fechaConcesion = $fechaConcesion;
        $this->entidadBancaria = $entidadBancaria;
        $this->numeroPrestamo = $numeroPrestamo;
        $this->tipoInteres = $tipoInteres !== null ? (string) $tipoInteres->value : null;
        $this->estado = $estado;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function cambiarEstado(PrestamoEstado $nuevoEstado): void
    {
        $this->estado = $nuevoEstado;
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

    public function id(): ?PrestamoId
    {
        return $this->id !== null ? PrestamoId::fromInt($this->id) : null;
    }

    public function local(): Local
    {
        return $this->local;
    }

    public function entidadBancaria(): ?string
    {
        return $this->entidadBancaria;
    }

    public function numeroPrestamo(): ?string
    {
        return $this->numeroPrestamo;
    }

    public function capitalSolicitado(): CapitalSolicitado
    {
        return CapitalSolicitado::fromFloat((float) $this->capitalSolicitado);
    }

    public function totalADevolver(): TotalADevolver
    {
        return TotalADevolver::fromFloat((float) $this->totalADevolver);
    }

    public function tipoInteres(): ?TipoInteres
    {
        return $this->tipoInteres !== null ? TipoInteres::fromFloat((float) $this->tipoInteres) : null;
    }

    public function fechaConcesion(): \DateTimeImmutable
    {
        return $this->fechaConcesion;
    }

    public function estado(): PrestamoEstado
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
