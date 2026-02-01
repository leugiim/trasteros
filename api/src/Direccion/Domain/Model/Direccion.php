<?php

declare(strict_types=1);

namespace App\Direccion\Domain\Model;

use App\Users\Domain\Model\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'direccion')]
#[ORM\HasLifecycleCallbacks]
class Direccion
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    private ?string $tipoVia = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $nombreVia;

    #[ORM\Column(type: Types::STRING, length: 10, nullable: true)]
    private ?string $numero = null;

    #[ORM\Column(type: Types::STRING, length: 10, nullable: true)]
    private ?string $piso = null;

    #[ORM\Column(type: Types::STRING, length: 10, nullable: true)]
    private ?string $puerta = null;

    #[ORM\Column(type: Types::STRING, length: 10)]
    private string $codigoPostal;

    #[ORM\Column(type: Types::STRING, length: 100)]
    private string $ciudad;

    #[ORM\Column(type: Types::STRING, length: 100)]
    private string $provincia;

    #[ORM\Column(type: Types::STRING, length: 100)]
    private string $pais;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 8, nullable: true)]
    private ?string $latitud = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 11, scale: 8, nullable: true)]
    private ?string $longitud = null;

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
        string $nombreVia,
        CodigoPostal $codigoPostal,
        string $ciudad,
        string $provincia,
        string $pais,
        ?string $tipoVia,
        ?string $numero,
        ?string $piso,
        ?string $puerta,
        Coordenadas $coordenadas
    ) {
        $this->nombreVia = $nombreVia;
        $this->codigoPostal = $codigoPostal->value;
        $this->ciudad = $ciudad;
        $this->provincia = $provincia;
        $this->pais = $pais;
        $this->tipoVia = $tipoVia;
        $this->numero = $numero;
        $this->piso = $piso;
        $this->puerta = $puerta;
        $this->latitud = $coordenadas->latitud !== null ? (string) $coordenadas->latitud : null;
        $this->longitud = $coordenadas->longitud !== null ? (string) $coordenadas->longitud : null;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public static function create(
        string $nombreVia,
        CodigoPostal $codigoPostal,
        string $ciudad,
        string $provincia,
        string $pais = 'España',
        ?string $tipoVia = null,
        ?string $numero = null,
        ?string $piso = null,
        ?string $puerta = null,
        ?Coordenadas $coordenadas = null
    ): self {
        return new self(
            nombreVia: $nombreVia,
            codigoPostal: $codigoPostal,
            ciudad: $ciudad,
            provincia: $provincia,
            pais: $pais,
            tipoVia: $tipoVia,
            numero: $numero,
            piso: $piso,
            puerta: $puerta,
            coordenadas: $coordenadas ?? Coordenadas::empty()
        );
    }

    public function update(
        string $nombreVia,
        CodigoPostal $codigoPostal,
        string $ciudad,
        string $provincia,
        string $pais,
        ?string $tipoVia,
        ?string $numero,
        ?string $piso,
        ?string $puerta,
        Coordenadas $coordenadas
    ): void {
        $this->nombreVia = $nombreVia;
        $this->codigoPostal = $codigoPostal->value;
        $this->ciudad = $ciudad;
        $this->provincia = $provincia;
        $this->pais = $pais;
        $this->tipoVia = $tipoVia;
        $this->numero = $numero;
        $this->piso = $piso;
        $this->puerta = $puerta;
        $this->latitud = $coordenadas->latitud !== null ? (string) $coordenadas->latitud : null;
        $this->longitud = $coordenadas->longitud !== null ? (string) $coordenadas->longitud : null;
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

    public function id(): ?DireccionId
    {
        return $this->id !== null ? DireccionId::fromInt($this->id) : null;
    }

    public function tipoVia(): ?string
    {
        return $this->tipoVia;
    }

    public function nombreVia(): string
    {
        return $this->nombreVia;
    }

    public function numero(): ?string
    {
        return $this->numero;
    }

    public function piso(): ?string
    {
        return $this->piso;
    }

    public function puerta(): ?string
    {
        return $this->puerta;
    }

    public function codigoPostal(): CodigoPostal
    {
        return CodigoPostal::fromString($this->codigoPostal);
    }

    public function ciudad(): string
    {
        return $this->ciudad;
    }

    public function provincia(): string
    {
        return $this->provincia;
    }

    public function pais(): string
    {
        return $this->pais;
    }

    public function coordenadas(): Coordenadas
    {
        $latitud = $this->latitud !== null ? (float) $this->latitud : null;
        $longitud = $this->longitud !== null ? (float) $this->longitud : null;

        return Coordenadas::create($latitud, $longitud);
    }

    public function direccionCompleta(): string
    {
        $partes = [];

        if ($this->tipoVia !== null) {
            $partes[] = $this->tipoVia;
        }

        $partes[] = $this->nombreVia;

        if ($this->numero !== null) {
            $partes[] = $this->numero;
        }

        $lineaDireccion = implode(' ', $partes);

        if ($this->piso !== null || $this->puerta !== null) {
            $lineaDireccion .= ', ';
            if ($this->piso !== null) {
                $lineaDireccion .= $this->piso;
            }
            if ($this->puerta !== null) {
                $lineaDireccion .= ' ' . $this->puerta;
            }
        }

        return sprintf(
            '%s, %s %s, %s',
            $lineaDireccion,
            $this->codigoPostal,
            $this->ciudad,
            $this->provincia
        );
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
