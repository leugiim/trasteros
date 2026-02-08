<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Persistence\Doctrine\Repository;

use App\Auth\Domain\Model\RefreshToken;
use App\Auth\Domain\Repository\RefreshTokenRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineRefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function save(RefreshToken $refreshToken): void
    {
        $this->entityManager->persist($refreshToken);
        $this->entityManager->flush();
    }

    public function findByToken(string $token): ?RefreshToken
    {
        return $this->entityManager->getRepository(RefreshToken::class)->findOneBy(['token' => $token]);
    }

    public function deleteByUserId(string $userId): void
    {
        $this->entityManager->createQueryBuilder()
            ->delete(RefreshToken::class, 'rt')
            ->where('rt.userId = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->execute();
    }

    public function deleteExpired(): void
    {
        $this->entityManager->createQueryBuilder()
            ->delete(RefreshToken::class, 'rt')
            ->where('rt.expiresAt < :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->execute();
    }
}
