<?php

declare(strict_types=1);

namespace App\Cliente\Domain\Model;

use App\Users\Domain\Model\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'cliente')]
#[ORM\HasLifecycleCallbacks]
class Cliente
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 100)]
    private string $nombre;

    #[ORM\Column(type: Types::STRING, length: 200)]
    private string $apellidos;

    #[ORM\Column(type: Types::STRING, length: 20, nullable: true)]
    private ?string $dniNie = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(type: Types::STRING, length: 20, nullable: true)]
    private ?string $telefono = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $activo = true;

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
        string $apellidos,
        ?DniNie $dniNie = null,
        ?Email $email = null,
        ?Telefono $telefono = null,
        bool $activo = true
    ) {
        $this->nombre = $nombre;
        $this->apellidos = $apellidos;
        $this->dniNie = $dniNie?->value;
        $this->email = $email?->value;
        $this->telefono = $telefono?->value;
        $this->activo = $activo;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public static function create(
        string $nombre,
        string $apellidos,
        ?DniNie $dniNie = null,
        ?Email $email = null,
        ?Telefono $telefono = null,
        bool $activo = true
    ): self {
        return new self(
            nombre: $nombre,
            apellidos: $apellidos,
            dniNie: $dniNie,
            email: $email,
            telefono: $telefono,
            activo: $activo
        );
    }

    public function update(
        string $nombre,
        string $apellidos,
        ?DniNie $dniNie,
        ?Email $email,
        ?Telefono $telefono,
        bool $activo
    ): void {
        $this->nombre = $nombre;
        $this->apellidos = $apellidos;
        $this->dniNie = $dniNie?->value;
        $this->email = $email?->value;
        $this->telefono = $telefono?->value;
        $this->activo = $activo;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function softDelete(User $deletedBy): void
    {
        $this->deletedAt = new \DateTimeImmutable();
        $this->deletedBy = $deletedBy;
        $this->activo = false;
    }

    public function restore(): void
    {
        $this->deletedAt = null;
        $this->deletedBy = null;
        $this->activo = true;
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function id(): ?ClienteId
    {
        return $this->id !== null ? ClienteId::fromInt($this->id) : null;
    }

    public function nombre(): string
    {
        return $this->nombre;
    }

    public function apellidos(): string
    {
        return $this->apellidos;
    }

    public function nombreCompleto(): string
    {
        return $this->nombre . ' ' . $this->apellidos;
    }

    public function dniNie(): ?DniNie
    {
        return $this->dniNie !== null ? DniNie::fromString($this->dniNie) : null;
    }

    public function email(): ?Email
    {
        return $this->email !== null ? Email::fromString($this->email) : null;
    }

    public function telefono(): ?Telefono
    {
        return $this->telefono !== null ? Telefono::fromString($this->telefono) : null;
    }

    public function activo(): bool
    {
        return $this->activo;
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
