<?php

declare(strict_types=1);

namespace App\Users\Domain\Repository;

use App\Users\Domain\Model\User;
use App\Users\Domain\Model\UserEmail;
use App\Users\Domain\Model\UserId;

interface UserRepositoryInterface
{
    public function save(User $user): void;

    public function remove(User $user): void;

    public function findById(UserId $id): ?User;

    public function findByEmail(UserEmail $email): ?User;

    public function existsByEmail(UserEmail $email): bool;

    /**
     * @return User[]
     */
    public function findAll(): array;

    /**
     * @return User[]
     */
    public function findByActive(bool $active): array;

    public function count(): int;
}
