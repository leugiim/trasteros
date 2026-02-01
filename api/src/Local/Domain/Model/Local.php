<?php

declare(strict_types=1);

namespace App\Local\Domain\Model;

use App\Direccion\Domain\Model\Direccion;
use App\Users\Domain\Model\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'local')]
#[ORM\HasLifecycleCallbacks]
class Local
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $nombre;

    #[ORM\ManyToOne(targetEntity: Direccion::class)]
    #[ORM\JoinColumn(name: 'direccion_id', referencedColumnName: 'id', nullable: false)]
    private Direccion $direccion;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $superficieTotal = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $numeroTrasteros = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $fechaCompra = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, nullable: true)]
    private ?string $precioCompra = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    private ?string $referenciaCatastral = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, nullable: true)]
    private ?string $valorCatastral = null;

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
        string $nombre,
        Direccion $direccion,
        ?float $superficieTotal,
        ?int $numeroTrasteros,
        ?\DateTimeImmutable $fechaCompra,
        ?float $precioCompra,
        ?ReferenciaCatastral $referenciaCatastral,
        ?float $valorCatastral
    ) {
        $this->nombre = $nombre;
        $this->direccion = $direccion;
        $this->superficieTotal = $superficieTotal !== null ? (string) $superficieTotal : null;
        $this->numeroTrasteros = $numeroTrasteros;
        $this->fechaCompra = $fechaCompra;
        $this->precioCompra = $precioCompra !== null ? (string) $precioCompra : null;
        $this->referenciaCatastral = $referenciaCatastral?->value;
        $this->valorCatastral = $valorCatastral !== null ? (string) $valorCatastral : null;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public static function create(
        string $nombre,
        Direccion $direccion,
        ?float $superficieTotal = null,
        ?int $numeroTrasteros = null,
        ?\DateTimeImmutable $fechaCompra = null,
        ?float $precioCompra = null,
        ?ReferenciaCatastral $referenciaCatastral = null,
        ?float $valorCatastral = null
    ): self {
        return new self(
            nombre: $nombre,
            direccion: $direccion,
            superficieTotal: $superficieTotal,
            numeroTrasteros: $numeroTrasteros,
            fechaCompra: $fechaCompra,
            precioCompra: $precioCompra,
            referenciaCatastral: $referenciaCatastral,
            valorCatastral: $valorCatastral
        );
    }

    public function update(
        string $nombre,
        Direccion $direccion,
        ?float $superficieTotal,
        ?int $numeroTrasteros,
        ?\DateTimeImmutable $fechaCompra,
        ?float $precioCompra,
        ?ReferenciaCatastral $referenciaCatastral,
        ?float $valorCatastral
    ): void {
        $this->nombre = $nombre;
        $this->direccion = $direccion;
        $this->superficieTotal = $superficieTotal !== null ? (string) $superficieTotal : null;
        $this->numeroTrasteros = $numeroTrasteros;
        $this->fechaCompra = $fechaCompra;
        $this->precioCompra = $precioCompra !== null ? (string) $precioCompra : null;
        $this->referenciaCatastral = $referenciaCatastral?->value;
        $this->valorCatastral = $valorCatastral !== null ? (string) $valorCatastral : null;
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

    public function id(): ?LocalId
    {
        return $this->id !== null ? LocalId::fromInt($this->id) : null;
    }

    public function nombre(): string
    {
        return $this->nombre;
    }

    public function direccion(): Direccion
    {
        return $this->direccion;
    }

    public function superficieTotal(): ?float
    {
        return $this->superficieTotal !== null ? (float) $this->superficieTotal : null;
    }

    public function numeroTrasteros(): ?int
    {
        return $this->numeroTrasteros;
    }

    public function fechaCompra(): ?\DateTimeImmutable
    {
        return $this->fechaCompra;
    }

    public function precioCompra(): ?float
    {
        return $this->precioCompra !== null ? (float) $this->precioCompra : null;
    }

    public function referenciaCatastral(): ?ReferenciaCatastral
    {
        return $this->referenciaCatastral !== null
            ? ReferenciaCatastral::fromString($this->referenciaCatastral)
            : null;
    }

    public function valorCatastral(): ?float
    {
        return $this->valorCatastral !== null ? (float) $this->valorCatastral : null;
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
