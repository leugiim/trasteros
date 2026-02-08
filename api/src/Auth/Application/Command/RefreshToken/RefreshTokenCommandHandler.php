<?php

declare(strict_types=1);

namespace App\Auth\Application\Command\RefreshToken;

use App\Auth\Application\DTO\LoginResponse;
use App\Auth\Domain\Exception\InvalidRefreshTokenException;
use App\Auth\Domain\Model\RefreshToken;
use App\Auth\Domain\Repository\RefreshTokenRepositoryInterface;
use App\Users\Domain\Model\UserId;
use App\Users\Domain\Repository\UserRepositoryInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class RefreshTokenCommandHandler
{
    public function __construct(
        private RefreshTokenRepositoryInterface $refreshTokenRepository,
        private UserRepositoryInterface $userRepository,
        private JWTTokenManagerInterface $jwtManager
    ) {
    }

    public function __invoke(RefreshTokenCommand $command): LoginResponse
    {
        $existingToken = $this->refreshTokenRepository->findByToken($command->refreshToken);

        if ($existingToken === null) {
            throw InvalidRefreshTokenException::invalid();
        }

        if ($existingToken->isExpired()) {
            $this->refreshTokenRepository->deleteByUserId($existingToken->userId());
            throw InvalidRefreshTokenException::expired();
        }

        $user = $this->userRepository->findById(UserId::fromString($existingToken->userId()));

        if ($user === null || !$user->isActivo()) {
            $this->refreshTokenRepository->deleteByUserId($existingToken->userId());
            throw InvalidRefreshTokenException::invalid();
        }

        $jwt = $this->jwtManager->create($user);

        $this->refreshTokenRepository->deleteByUserId($user->id()->value);
        $newRefreshToken = RefreshToken::create($user->id()->value);
        $this->refreshTokenRepository->save($newRefreshToken);

        return LoginResponse::create($jwt, $newRefreshToken->token(), $user);
    }
}
