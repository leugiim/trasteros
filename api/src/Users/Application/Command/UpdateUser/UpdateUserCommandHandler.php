<?php

declare(strict_types=1);

namespace App\Users\Application\Command\UpdateUser;

use App\Users\Application\DTO\UserResponse;
use App\Users\Domain\Event\UserUpdated;
use App\Users\Domain\Exception\UserAlreadyExistsException;
use App\Users\Domain\Exception\UserNotFoundException;
use App\Users\Domain\Model\UserEmail;
use App\Users\Domain\Model\UserId;
use App\Users\Domain\Model\UserRole;
use App\Users\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsMessageHandler]
final readonly class UpdateUserCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private MessageBusInterface $eventBus
    ) {
    }

    public function __invoke(UpdateUserCommand $command): UserResponse
    {
        $userId = UserId::fromString($command->id);
        $user = $this->userRepository->findById($userId);

        if ($user === null) {
            throw UserNotFoundException::withId($command->id);
        }

        $newEmail = UserEmail::fromString($command->email);

        if (!$user->email()->equals($newEmail) && $this->userRepository->existsByEmail($newEmail)) {
            throw UserAlreadyExistsException::withEmail($command->email);
        }

        $user->update(
            nombre: $command->nombre,
            email: $newEmail,
            rol: UserRole::fromString($command->rol),
            activo: $command->activo
        );

        if ($command->password !== null) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $command->password);
            $user->changePassword($hashedPassword);
        }

        $this->userRepository->save($user);

        $this->eventBus->dispatch(
            UserUpdated::create(
                userId: $user->id()->value,
                email: $user->email()->value,
                nombre: $user->nombre(),
                rol: $user->rol()->value,
                activo: $user->isActivo()
            )
        );

        return UserResponse::fromUser($user);
    }
}
