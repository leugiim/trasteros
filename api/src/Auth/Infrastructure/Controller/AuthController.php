<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Controller;

use App\Auth\Application\Command\Login\LoginCommand;
use App\Auth\Application\Command\RefreshToken\RefreshTokenCommand;
use App\Auth\Application\DTO\LoginRequest;
use App\Auth\Application\DTO\LoginResponse;
use App\Auth\Application\DTO\RefreshTokenRequest;
use App\Auth\Domain\Exception\InvalidCredentialsException;
use App\Auth\Domain\Exception\InvalidRefreshTokenException;
use App\Auth\Domain\Exception\UserInactiveException;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/auth')]
#[OA\Tag(name: 'Auth')]
final class AuthController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $commandBus
    ) {
    }

    #[Route('/login', name: 'auth_login', methods: ['POST'])]
    #[OA\Post(
        summary: 'Iniciar sesion',
        description: 'Autentica un usuario y devuelve un token JWT junto con un refresh token'
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['email', 'password'],
            properties: [
                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'admin@trasteros.com'),
                new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password123')
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Login exitoso',
        content: new OA\JsonContent(ref: '#/components/schemas/LoginResponse')
    )]
    #[OA\Response(
        response: 401,
        description: 'Credenciales invalidas',
        content: new OA\JsonContent(ref: '#/components/schemas/Error')
    )]
    #[OA\Response(
        response: 403,
        description: 'Usuario inactivo',
        content: new OA\JsonContent(ref: '#/components/schemas/Error')
    )]
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
        } catch (HandlerFailedException $e) {
            foreach ($e->getWrappedExceptions() as $nestedException) {
                if ($nestedException instanceof InvalidCredentialsException) {
                    return $this->json([
                        'error' => [
                            'message' => $nestedException->getMessage(),
                            'code' => 'INVALID_CREDENTIALS',
                        ],
                    ], Response::HTTP_UNAUTHORIZED);
                }

                if ($nestedException instanceof UserInactiveException) {
                    return $this->json([
                        'error' => [
                            'message' => $nestedException->getMessage(),
                            'code' => 'USER_INACTIVE',
                        ],
                    ], Response::HTTP_FORBIDDEN);
                }
            }

            throw $e;
        }
    }

    #[Route('/refresh', name: 'auth_refresh', methods: ['POST'])]
    #[OA\Post(
        summary: 'Refrescar token',
        description: 'Genera un nuevo JWT y refresh token a partir de un refresh token valido'
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['refreshToken'],
            properties: [
                new OA\Property(property: 'refreshToken', type: 'string', example: 'a1b2c3d4e5f6...')
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Token refrescado exitosamente',
        content: new OA\JsonContent(ref: '#/components/schemas/LoginResponse')
    )]
    #[OA\Response(
        response: 401,
        description: 'Refresh token invalido o expirado',
        content: new OA\JsonContent(ref: '#/components/schemas/Error')
    )]
    public function refresh(#[MapRequestPayload] RefreshTokenRequest $request): JsonResponse
    {
        try {
            $envelope = $this->commandBus->dispatch(new RefreshTokenCommand(
                refreshToken: $request->refreshToken
            ));

            $handledStamp = $envelope->last(HandledStamp::class);

            /** @var LoginResponse $response */
            $response = $handledStamp->getResult();

            return $this->json($response->toArray());
        } catch (HandlerFailedException $e) {
            foreach ($e->getWrappedExceptions() as $nestedException) {
                if ($nestedException instanceof InvalidRefreshTokenException) {
                    $code = str_contains($nestedException->getMessage(), 'expirado')
                        ? 'EXPIRED_REFRESH_TOKEN'
                        : 'INVALID_REFRESH_TOKEN';

                    return $this->json([
                        'error' => [
                            'message' => $nestedException->getMessage(),
                            'code' => $code,
                        ],
                    ], Response::HTTP_UNAUTHORIZED);
                }
            }

            throw $e;
        }
    }
}
