<?php

declare(strict_types=1);

namespace App\Users\Infrastructure\Controller;

use App\Auth\Infrastructure\Attribute\Auth;
use App\Users\Application\Command\CreateUser\CreateUserCommand;
use App\Users\Application\Command\DeleteUser\DeleteUserCommand;
use App\Users\Application\Command\UpdateUser\UpdateUserCommand;
use App\Users\Application\DTO\UserRequest;
use App\Users\Application\DTO\UserResponse;
use App\Users\Application\Query\FindUser\FindUserQuery;
use App\Users\Application\Query\ListUsers\ListUsersQuery;
use App\Users\Domain\Model\User;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/users')]
#[Auth]
#[OA\Tag(name: 'Users')]
final class UserController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly MessageBusInterface $queryBus
    ) {
    }

    #[Route('', name: 'users_list', methods: ['GET'])]
    #[OA\Get(summary: 'Listar usuarios', description: 'Obtiene la lista de todos los usuarios del sistema')]
    #[OA\Parameter(name: 'activo', in: 'query', description: 'Filtrar por estado activo', schema: new OA\Schema(type: 'boolean'))]
    #[OA\Response(
        response: 200,
        description: 'Lista de usuarios',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/User')),
                new OA\Property(property: 'meta', ref: '#/components/schemas/PaginatedMeta')
            ]
        )
    )]
    public function list(Request $request): JsonResponse
    {
        $activo = $request->query->get('activo');
        $activoFilter = $activo !== null ? filter_var($activo, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : null;

        $envelope = $this->queryBus->dispatch(new ListUsersQuery($activoFilter));
        $handledStamp = $envelope->last(HandledStamp::class);

        /** @var UserResponse[] $users */
        $users = $handledStamp->getResult();

        return $this->json([
            'data' => array_map(fn(UserResponse $user) => $user->toArray(), $users),
            'meta' => [
                'total' => count($users),
            ],
        ]);
    }

    #[Route('/me', name: 'users_me', methods: ['GET'])]
    #[OA\Get(
        summary: 'Obtener usuario actual',
        description: 'Devuelve los datos del usuario autenticado'
    )]
    #[OA\Response(
        response: 200,
        description: 'Datos del usuario',
        content: new OA\JsonContent(ref: '#/components/schemas/User')
    )]
    #[OA\Response(
        response: 401,
        description: 'No autenticado',
        content: new OA\JsonContent(ref: '#/components/schemas/Error')
    )]
    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('authenticated_user');

        return $this->json(UserResponse::fromUser($user)->toArray());
    }

    #[Route('/{id}', name: 'users_show', methods: ['GET'])]
    #[OA\Get(summary: 'Obtener usuario', description: 'Obtiene los datos de un usuario por su ID')]
    #[OA\Parameter(name: 'id', in: 'path', description: 'UUID del usuario', schema: new OA\Schema(type: 'string', format: 'uuid'))]
    #[OA\Response(response: 200, description: 'Usuario encontrado', content: new OA\JsonContent(ref: '#/components/schemas/User'))]
    #[OA\Response(response: 404, description: 'Usuario no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Error'))]
    public function show(string $id): JsonResponse
    {
        $envelope = $this->queryBus->dispatch(new FindUserQuery($id));
        $handledStamp = $envelope->last(HandledStamp::class);

        /** @var UserResponse $user */
        $user = $handledStamp->getResult();

        return $this->json($user->toArray());
    }

    #[Route('', name: 'users_create', methods: ['POST'])]
    #[OA\Post(summary: 'Crear usuario', description: 'Crea un nuevo usuario en el sistema')]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['nombre', 'email', 'password'],
            properties: [
                new OA\Property(property: 'nombre', type: 'string', maxLength: 100, example: 'Juan García'),
                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'juan@example.com'),
                new OA\Property(property: 'password', type: 'string', minLength: 6, example: 'password123'),
                new OA\Property(property: 'rol', type: 'string', enum: ['admin', 'gestor', 'readonly'], default: 'gestor'),
                new OA\Property(property: 'activo', type: 'boolean', default: true)
            ]
        )
    )]
    #[OA\Response(response: 201, description: 'Usuario creado', content: new OA\JsonContent(ref: '#/components/schemas/User'))]
    #[OA\Response(response: 400, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))]
    #[OA\Response(response: 409, description: 'El usuario ya existe', content: new OA\JsonContent(ref: '#/components/schemas/Error'))]
    public function create(#[MapRequestPayload] UserRequest $request): JsonResponse
    {
        if ($request->password === null) {
            return $this->json([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => 'VALIDATION_ERROR',
                    'details' => [
                        'password' => ['La contraseña es obligatoria'],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        $envelope = $this->commandBus->dispatch(new CreateUserCommand(
            nombre: $request->nombre,
            email: $request->email,
            password: $request->password,
            rol: $request->rol,
            activo: $request->activo
        ));

        $handledStamp = $envelope->last(HandledStamp::class);

        /** @var UserResponse $user */
        $user = $handledStamp->getResult();

        return $this->json($user->toArray(), Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'users_update', methods: ['PUT'])]
    #[OA\Put(summary: 'Actualizar usuario', description: 'Actualiza los datos de un usuario existente')]
    #[OA\Parameter(name: 'id', in: 'path', description: 'UUID del usuario', schema: new OA\Schema(type: 'string', format: 'uuid'))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['nombre', 'email'],
            properties: [
                new OA\Property(property: 'nombre', type: 'string', maxLength: 100),
                new OA\Property(property: 'email', type: 'string', format: 'email'),
                new OA\Property(property: 'password', type: 'string', minLength: 6, nullable: true),
                new OA\Property(property: 'rol', type: 'string', enum: ['admin', 'gestor', 'readonly']),
                new OA\Property(property: 'activo', type: 'boolean')
            ]
        )
    )]
    #[OA\Response(response: 200, description: 'Usuario actualizado', content: new OA\JsonContent(ref: '#/components/schemas/User'))]
    #[OA\Response(response: 400, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))]
    #[OA\Response(response: 404, description: 'Usuario no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Error'))]
    #[OA\Response(response: 409, description: 'Email ya en uso', content: new OA\JsonContent(ref: '#/components/schemas/Error'))]
    public function update(string $id, #[MapRequestPayload] UserRequest $request): JsonResponse
    {
        $envelope = $this->commandBus->dispatch(new UpdateUserCommand(
            id: $id,
            nombre: $request->nombre,
            email: $request->email,
            rol: $request->rol,
            activo: $request->activo,
            password: $request->password
        ));

        $handledStamp = $envelope->last(HandledStamp::class);

        /** @var UserResponse $user */
        $user = $handledStamp->getResult();

        return $this->json($user->toArray());
    }

    #[Route('/{id}', name: 'users_delete', methods: ['DELETE'])]
    #[OA\Delete(summary: 'Eliminar usuario', description: 'Elimina un usuario del sistema')]
    #[OA\Parameter(name: 'id', in: 'path', description: 'UUID del usuario', schema: new OA\Schema(type: 'string', format: 'uuid'))]
    #[OA\Response(response: 204, description: 'Usuario eliminado')]
    #[OA\Response(response: 404, description: 'Usuario no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Error'))]
    public function delete(string $id): JsonResponse
    {
        $this->commandBus->dispatch(new DeleteUserCommand($id));

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
