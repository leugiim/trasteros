<?php

declare(strict_types=1);

namespace App\Users\Infrastructure\Controller;

use App\Users\Application\Command\CreateUser\CreateUserCommand;
use App\Users\Application\Command\DeleteUser\DeleteUserCommand;
use App\Users\Application\Command\UpdateUser\UpdateUserCommand;
use App\Users\Application\DTO\UserRequest;
use App\Users\Application\DTO\UserResponse;
use App\Users\Application\Query\FindUser\FindUserQuery;
use App\Users\Application\Query\ListUsers\ListUsersQuery;
use App\Users\Domain\Exception\InvalidEmailException;
use App\Users\Domain\Exception\UserAlreadyExistsException;
use App\Users\Domain\Exception\UserNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;
use App\Auth\Infrastructure\Attribute\Auth;

#[Route('/api/users')]
#[Auth]
final class UserController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly MessageBusInterface $queryBus
    ) {
    }

    #[Route('', name: 'users_list', methods: ['GET'])]
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

    #[Route('/{id}', name: 'users_show', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        try {
            $envelope = $this->queryBus->dispatch(new FindUserQuery($id));
            $handledStamp = $envelope->last(HandledStamp::class);

            /** @var UserResponse $user */
            $user = $handledStamp->getResult();

            return $this->json($user->toArray());
        } catch (UserNotFoundException $e) {
            return $this->json([
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => 'USER_NOT_FOUND',
                ],
            ], Response::HTTP_NOT_FOUND);
        }
    }

    #[Route('', name: 'users_create', methods: ['POST'])]
    public function create(#[MapRequestPayload] UserRequest $request): JsonResponse
    {
        try {
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
        } catch (UserAlreadyExistsException $e) {
            return $this->json([
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => 'USER_ALREADY_EXISTS',
                ],
            ], Response::HTTP_CONFLICT);
        } catch (InvalidEmailException $e) {
            return $this->json([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => 'VALIDATION_ERROR',
                    'details' => [
                        'email' => [$e->getMessage()],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'users_update', methods: ['PUT'])]
    public function update(string $id, #[MapRequestPayload] UserRequest $request): JsonResponse
    {
        try {
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
        } catch (UserNotFoundException $e) {
            return $this->json([
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => 'USER_NOT_FOUND',
                ],
            ], Response::HTTP_NOT_FOUND);
        } catch (UserAlreadyExistsException $e) {
            return $this->json([
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => 'USER_ALREADY_EXISTS',
                ],
            ], Response::HTTP_CONFLICT);
        } catch (InvalidEmailException $e) {
            return $this->json([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => 'VALIDATION_ERROR',
                    'details' => [
                        'email' => [$e->getMessage()],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'users_delete', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {
        try {
            $this->commandBus->dispatch(new DeleteUserCommand($id));

            return $this->json(null, Response::HTTP_NO_CONTENT);
        } catch (UserNotFoundException $e) {
            return $this->json([
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => 'USER_NOT_FOUND',
                ],
            ], Response::HTTP_NOT_FOUND);
        }
    }
}
