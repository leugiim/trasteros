<?php

declare(strict_types=1);

namespace App\Users\Application\Command\DeleteUser;

use App\Users\Domain\Event\UserDeleted;
use App\Users\Domain\Exception\UserNotFoundException;
use App\Users\Domain\Model\UserId;
use App\Users\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final readonly class DeleteUserCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private MessageBusInterface $eventBus
    ) {
    }

    public function __invoke(DeleteUserCommand $command): void
    {
        $userId = UserId::fromString($command->id);
        $user = $this->userRepository->findById($userId);

        if ($user === null) {
            throw UserNotFoundException::withId($command->id);
        }

        $email = $user->email()->value;

        $this->userRepository->remove($user);

        $this->eventBus->dispatch(
            UserDeleted::create(
                userId: $command->id,
                email: $email
            )
        );
    }
}
