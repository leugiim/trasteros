<?php

declare(strict_types=1);

namespace App\Auth\Domain\Model;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'refresh_tokens')]
class RefreshToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 128, unique: true)]
    private string $token;

    #[ORM\Column(length: 36)]
    private string $userId;

    #[ORM\Column]
    private \DateTimeImmutable $expiresAt;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    private function __construct(string $token, string $userId, \DateTimeImmutable $expiresAt)
    {
        $this->token = $token;
        $this->userId = $userId;
        $this->expiresAt = $expiresAt;
        $this->createdAt = new \DateTimeImmutable();
    }

    public static function create(string $userId, int $ttlDays = 30): self
    {
        $token = bin2hex(random_bytes(64));
        $expiresAt = new \DateTimeImmutable("+{$ttlDays} days");

        return new self($token, $userId, $expiresAt);
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function token(): string
    {
        return $this->token;
    }

    public function userId(): string
    {
        return $this->userId;
    }

    public function expiresAt(): \DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function isExpired(): bool
    {
        return new \DateTimeImmutable() > $this->expiresAt;
    }
}
