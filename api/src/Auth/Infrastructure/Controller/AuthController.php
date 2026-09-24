<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Controller;

use App\Auth\Application\Command\Login\LoginCommand;
use App\Auth\Application\Command\RefreshToken\RefreshTokenCommand;
use App\Auth\Application\DTO\LoginRequest;
use App\Auth\Application\DTO\LoginResponse;
use App\Auth\Application\DTO\RefreshTokenRequest;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
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
        // Invalid credentials (401) and inactive users (403) are domain errors,
        // turned into responses by MessengerExceptionSubscriber
        $envelope = $this->commandBus->dispatch(new LoginCommand(
            email: $request->email,
            password: $request->password
        ));

        /** @var LoginResponse $response */
        $response = $envelope->last(HandledStamp::class)->getResult();

        return $this->json($response->toArray());
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
        // INVALID_REFRESH_TOKEN / EXPIRED_REFRESH_TOKEN (401) come from
        // InvalidRefreshTokenException, see MessengerExceptionSubscriber
        $envelope = $this->commandBus->dispatch(new RefreshTokenCommand(
            refreshToken: $request->refreshToken
        ));

        /** @var LoginResponse $response */
        $response = $envelope->last(HandledStamp::class)->getResult();

        return $this->json($response->toArray());
    }
}
