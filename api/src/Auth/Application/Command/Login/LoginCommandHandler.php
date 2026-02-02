<?php

declare(strict_types=1);

namespace App\Auth\Application\Command\Login;

use App\Auth\Application\DTO\LoginResponse;
use App\Auth\Domain\Exception\InvalidCredentialsException;
use App\Auth\Domain\Exception\UserInactiveException;
use App\Users\Domain\Model\UserEmail;
use App\Users\Domain\Repository\UserRepositoryInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsMessageHandler]
final readonly class LoginCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private JWTTokenManagerInterface $jwtManager
    ) {
    }

    public function __invoke(LoginCommand $command): LoginResponse
    {
        $email = UserEmail::fromString($command->email);
        $user = $this->userRepository->findByEmail($email);

        if ($user === null) {
            throw InvalidCredentialsException::create();
        }

        if (!$this->passwordHasher->isPasswordValid($user, $command->password)) {
            throw InvalidCredentialsException::create();
        }

        if (!$user->isActivo()) {
            throw UserInactiveException::withEmail($command->email);
        }

        $token = $this->jwtManager->create($user);

        return LoginResponse::create($token, $user);
    }
}
