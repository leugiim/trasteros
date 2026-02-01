<?php

declare(strict_types=1);

namespace App\Users\Application\Query\ListUsers;

use App\Users\Application\DTO\UserResponse;
use App\Users\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ListUsersQueryHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {
    }

    /**
     * @return UserResponse[]
     */
    public function __invoke(ListUsersQuery $query): array
    {
        if ($query->activo !== null) {
            $users = $this->userRepository->findByActive($query->activo);
        } else {
            $users = $this->userRepository->findAll();
        }

        return UserResponse::fromUsers($users);
    }
}
