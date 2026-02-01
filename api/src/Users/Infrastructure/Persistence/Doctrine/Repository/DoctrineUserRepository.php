<?php

declare(strict_types=1);

namespace App\Users\Infrastructure\Persistence\Doctrine\Repository;

use App\Users\Domain\Model\User;
use App\Users\Domain\Model\UserEmail;
use App\Users\Domain\Model\UserId;
use App\Users\Domain\Repository\UserRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
final class DoctrineUserRepository extends ServiceEntityRepository implements UserRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function save(User $user): void
    {
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function remove(User $user): void
    {
        $this->getEntityManager()->remove($user);
        $this->getEntityManager()->flush();
    }

    public function findById(UserId $id): ?User
    {
        return $this->find($id->value);
    }

    public function findByEmail(UserEmail $email): ?User
    {
        return $this->findOneBy(['email' => $email->value]);
    }

    public function existsByEmail(UserEmail $email): bool
    {
        return $this->findByEmail($email) !== null;
    }

    /**
     * @return User[]
     */
    public function findAll(): array
    {
        return parent::findAll();
    }

    /**
     * @return User[]
     */
    public function findByActive(bool $active): array
    {
        return $this->findBy(['activo' => $active]);
    }

    public function count(array $criteria = []): int
    {
        return parent::count($criteria);
    }
}
