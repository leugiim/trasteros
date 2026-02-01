<?php

declare(strict_types=1);

namespace App\Users\Application\Command\CreateUser;

use App\Users\Application\DTO\UserResponse;
use App\Users\Domain\Event\UserCreated;
use App\Users\Domain\Exception\UserAlreadyExistsException;
use App\Users\Domain\Model\User;
use App\Users\Domain\Model\UserEmail;
use App\Users\Domain\Model\UserId;
use App\Users\Domain\Model\UserRole;
use App\Users\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsMessageHandler]
final readonly class CreateUserCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private MessageBusInterface $eventBus
    ) {
    }

    public function __invoke(CreateUserCommand $command): UserResponse
    {
        $email = UserEmail::fromString($command->email);

        if ($this->userRepository->existsByEmail($email)) {
            throw UserAlreadyExistsException::withEmail($command->email);
        }

        $userId = UserId::generate();
        $role = UserRole::fromString($command->rol);

        $user = User::create(
            id: $userId,
            nombre: $command->nombre,
            email: $email,
            hashedPassword: '',
            rol: $role,
            activo: $command->activo
        );

        $hashedPassword = $this->passwordHasher->hashPassword($user, $command->password);
        $user->changePassword($hashedPassword);

        $this->userRepository->save($user);

        $this->eventBus->dispatch(
            UserCreated::create(
                userId: $userId->value,
                email: $email->value,
                nombre: $command->nombre,
                rol: $role->value
            )
        );

        return UserResponse::fromUser($user);
    }
}
