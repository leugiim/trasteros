<?php

declare(strict_types=1);

namespace App\Trastero\Domain\Model;

use App\Local\Domain\Model\Local;
use App\Users\Domain\Model\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'trastero')]
#[ORM\UniqueConstraint(name: 'unique_trastero_local', columns: ['local_id', 'numero'])]
#[ORM\Index(name: 'idx_trastero_estado', columns: ['estado'])]
#[ORM\Index(name: 'idx_trastero_deleted_at', columns: ['deleted_at'])]
#[ORM\HasLifecycleCallbacks]
class Trastero
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Local::class)]
    #[ORM\JoinColumn(name: 'local_id', referencedColumnName: 'id', nullable: false)]
    private Local $local;

    #[ORM\Column(type: Types::STRING, length: 20)]
    private string $numero;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true)]
    private ?string $nombre = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 6, scale: 2)]
    private string $superficie;

    #[ORM\Column(type: Types::DECIMAL, precision: 8, scale: 2)]
    private string $precioMensual;

    #[ORM\Column(type: Types::STRING, length: 50, enumType: TrasteroEstado::class)]
    private TrasteroEstado $estado;

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
        string $numero,
        Superficie $superficie,
        PrecioMensual $precioMensual,
        ?string $nombre = null,
        ?TrasteroEstado $estado = null
    ) {
        $this->local = $local;
        $this->numero = $numero;
        $this->superficie = (string) $superficie->value;
        $this->precioMensual = (string) $precioMensual->value;
        $this->nombre = $nombre;
        $this->estado = $estado ?? TrasteroEstado::DISPONIBLE;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public static function create(
        Local $local,
        string $numero,
        Superficie $superficie,
        PrecioMensual $precioMensual,
        ?string $nombre = null,
        ?TrasteroEstado $estado = null
    ): self {
        return new self(
            local: $local,
            numero: $numero,
            superficie: $superficie,
            precioMensual: $precioMensual,
            nombre: $nombre,
            estado: $estado
        );
    }

    public function update(
        Local $local,
        string $numero,
        Superficie $superficie,
        PrecioMensual $precioMensual,
        ?string $nombre,
        TrasteroEstado $estado
    ): void {
        $this->local = $local;
        $this->numero = $numero;
        $this->superficie = (string) $superficie->value;
        $this->precioMensual = (string) $precioMensual->value;
        $this->nombre = $nombre;
        $this->estado = $estado;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function changeEstado(TrasteroEstado $estado): void
    {
        $this->estado = $estado;
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

    public function id(): ?TrasteroId
    {
        return $this->id !== null ? TrasteroId::fromInt($this->id) : null;
    }

    public function local(): Local
    {
        return $this->local;
    }

    public function numero(): string
    {
        return $this->numero;
    }

    public function nombre(): ?string
    {
        return $this->nombre;
    }

    public function superficie(): Superficie
    {
        return Superficie::fromFloat((float) $this->superficie);
    }

    public function precioMensual(): PrecioMensual
    {
        return PrecioMensual::fromFloat((float) $this->precioMensual);
    }

    public function estado(): TrasteroEstado
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
