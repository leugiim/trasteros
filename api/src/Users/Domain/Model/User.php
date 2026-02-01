<?php

declare(strict_types=1);

namespace App\Users\Domain\Model;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity]
#[ORM\Table(name: 'usuario')]
#[ORM\HasLifecycleCallbacks]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\Column(type: 'string', length: 100)]
    private string $nombre;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private string $email;

    #[ORM\Column(type: 'string', length: 255)]
    private string $password;

    #[ORM\Column(type: 'string', length: 20, enumType: UserRole::class)]
    private UserRole $rol;

    #[ORM\Column(type: 'boolean')]
    private bool $activo;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    private function __construct(
        UserId $id,
        string $nombre,
        UserEmail $email,
        string $hashedPassword,
        UserRole $rol,
        bool $activo
    ) {
        $this->id = $id->value;
        $this->nombre = $nombre;
        $this->email = $email->value;
        $this->password = $hashedPassword;
        $this->rol = $rol;
        $this->activo = $activo;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public static function create(
        UserId $id,
        string $nombre,
        UserEmail $email,
        string $hashedPassword,
        UserRole $rol = UserRole::GESTOR,
        bool $activo = true
    ): self {
        return new self($id, $nombre, $email, $hashedPassword, $rol, $activo);
    }

    public function update(
        string $nombre,
        UserEmail $email,
        UserRole $rol,
        bool $activo
    ): void {
        $this->nombre = $nombre;
        $this->email = $email->value;
        $this->rol = $rol;
        $this->activo = $activo;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function changePassword(string $hashedPassword): void
    {
        $this->password = $hashedPassword;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function deactivate(): void
    {
        $this->activo = false;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function activate(): void
    {
        $this->activo = true;
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function id(): UserId
    {
        return UserId::fromString($this->id);
    }

    public function nombre(): string
    {
        return $this->nombre;
    }

    public function email(): UserEmail
    {
        return UserEmail::fromString($this->email);
    }

    public function rol(): UserRole
    {
        return $this->rol;
    }

    public function isActivo(): bool
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

    // UserInterface implementation
    public function getRoles(): array
    {
        return [$this->rol->toSymfonyRole()];
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }
}
