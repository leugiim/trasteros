<?php

declare(strict_types=1);

namespace App\Users\Application\Query\FindUser;

use App\Users\Application\DTO\UserResponse;
use App\Users\Domain\Exception\UserNotFoundException;
use App\Users\Domain\Model\UserId;
use App\Users\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class FindUserQueryHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function __invoke(FindUserQuery $query): UserResponse
    {
        $userId = UserId::fromString($query->id);
        $user = $this->userRepository->findById($userId);

        if ($user === null) {
            throw UserNotFoundException::withId($query->id);
        }

        return UserResponse::fromUser($user);
    }
}
