<?php

declare(strict_types=1);

namespace App\Auth\Domain\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * Long-lived token used to get a new JWT. Only a SHA-256 hash of the token is
 * stored (in the `token` column), so a database leak doesn't leak usable
 * tokens. Each one is single use: refreshing replaces it with a new one.
 */
#[ORM\Entity]
#[ORM\Table(name: 'refresh_tokens')]
class RefreshToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** SHA-256 hex of the token handed to the client */
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

    /**
     * @return array{0: self, 1: string} The entity and the plain token, which
     *                                   is only available at creation time
     */
    public static function issue(string $userId, int $ttlDays = 30): array
    {
        $plainToken = bin2hex(random_bytes(64));
        $expiresAt = new \DateTimeImmutable("+{$ttlDays} days");

        return [new self(self::hash($plainToken), $userId, $expiresAt), $plainToken];
    }

    public static function hash(string $plainToken): string
    {
        return hash('sha256', $plainToken);
    }

    public function id(): ?int
    {
        return $this->id;
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
