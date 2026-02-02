<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Controller;

use App\Auth\Application\Command\Login\LoginCommand;
use App\Auth\Application\DTO\LoginRequest;
use App\Auth\Application\DTO\LoginResponse;
use App\Auth\Domain\Exception\InvalidCredentialsException;
use App\Auth\Domain\Exception\UserInactiveException;
use App\Auth\Infrastructure\Attribute\Auth;
use App\Users\Domain\Model\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/auth')]
final class AuthController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $commandBus
    ) {
    }

    #[Route('/login', name: 'auth_login', methods: ['POST'])]
    public function login(#[MapRequestPayload] LoginRequest $request): JsonResponse
    {
        try {
            $envelope = $this->commandBus->dispatch(new LoginCommand(
                email: $request->email,
                password: $request->password
            ));

            $handledStamp = $envelope->last(HandledStamp::class);

            /** @var LoginResponse $response */
            $response = $handledStamp->getResult();

            return $this->json($response->toArray());
        } catch (InvalidCredentialsException $e) {
            return $this->json([
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => 'INVALID_CREDENTIALS',
                ],
            ], Response::HTTP_UNAUTHORIZED);
        } catch (UserInactiveException $e) {
            return $this->json([
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => 'USER_INACTIVE',
                ],
            ], Response::HTTP_FORBIDDEN);
        }
    }

    #[Route('/me', name: 'auth_me', methods: ['GET'])]
    #[Auth]
    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('authenticated_user');

        return $this->json([
            'id' => $user->id()->value,
            'email' => $user->email()->value,
            'nombre' => $user->nombre(),
            'rol' => $user->rol()->value,
        ]);
    }
}
