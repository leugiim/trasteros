<?php

declare(strict_types=1);

namespace App\Cliente\Infrastructure\Controller;

use App\Cliente\Application\Command\CreateCliente\CreateClienteCommand;
use App\Cliente\Application\Command\DeleteCliente\DeleteClienteCommand;
use App\Cliente\Application\Command\UpdateCliente\UpdateClienteCommand;
use App\Cliente\Application\DTO\ClienteRequest;
use App\Cliente\Application\DTO\ClienteResponse;
use App\Cliente\Application\Query\FindCliente\FindClienteQuery;
use App\Cliente\Application\Query\ListClientes\ListClientesQuery;
use App\Cliente\Domain\Exception\ClienteNotFoundException;
use App\Cliente\Domain\Exception\DuplicatedDniNieException;
use App\Cliente\Domain\Exception\DuplicatedEmailException;
use App\Cliente\Domain\Exception\InvalidDniNieException;
use App\Cliente\Domain\Exception\InvalidEmailException;
use App\Cliente\Domain\Exception\InvalidTelefonoException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;
use App\Auth\Infrastructure\Attribute\Auth;

#[Route('/api/clientes')]
#[Auth]
final class ClienteController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly MessageBusInterface $queryBus
    ) {
    }

    #[Route('', name: 'clientes_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $search = $request->query->get('search');
        $onlyActivos = $request->query->get('onlyActivos');

        $onlyActivosFilter = $onlyActivos !== null
            ? filter_var($onlyActivos, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
            : null;

        $envelope = $this->queryBus->dispatch(new ListClientesQuery(
            search: $search,
            onlyActivos: $onlyActivosFilter
        ));

        $handledStamp = $envelope->last(HandledStamp::class);

        /** @var ClienteResponse[] $clientes */
        $clientes = $handledStamp->getResult();

        return $this->json([
            'data' => array_map(fn(ClienteResponse $cliente) => $cliente->toArray(), $clientes),
            'meta' => [
                'total' => count($clientes),
            ],
        ]);
    }

    #[Route('/{id}', name: 'clientes_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        try {
            $envelope = $this->queryBus->dispatch(new FindClienteQuery($id));
            $handledStamp = $envelope->last(HandledStamp::class);

            /** @var ClienteResponse $cliente */
            $cliente = $handledStamp->getResult();

            return $this->json($cliente->toArray());
        } catch (ClienteNotFoundException $e) {
            return $this->json([
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => 'CLIENTE_NOT_FOUND',
                ],
            ], Response::HTTP_NOT_FOUND);
        }
    }

    #[Route('', name: 'clientes_create', methods: ['POST'])]
    public function create(#[MapRequestPayload] ClienteRequest $request): JsonResponse
    {
        try {
            $envelope = $this->commandBus->dispatch(new CreateClienteCommand(
                nombre: $request->nombre,
                apellidos: $request->apellidos,
                dniNie: $request->dniNie,
                email: $request->email,
                telefono: $request->telefono,
                activo: $request->activo
            ));

            $handledStamp = $envelope->last(HandledStamp::class);

            /** @var ClienteResponse $cliente */
            $cliente = $handledStamp->getResult();

            return $this->json($cliente->toArray(), Response::HTTP_CREATED);
        } catch (InvalidDniNieException $e) {
            return $this->json([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => 'VALIDATION_ERROR',
                    'details' => [
                        'dniNie' => [$e->getMessage()],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
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
        } catch (InvalidTelefonoException $e) {
            return $this->json([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => 'VALIDATION_ERROR',
                    'details' => [
                        'telefono' => [$e->getMessage()],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        } catch (DuplicatedDniNieException | DuplicatedEmailException $e) {
            return $this->json([
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => 'CONFLICT',
                ],
            ], Response::HTTP_CONFLICT);
        }
    }

    #[Route('/{id}', name: 'clientes_update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function update(int $id, #[MapRequestPayload] ClienteRequest $request): JsonResponse
    {
        try {
            $envelope = $this->commandBus->dispatch(new UpdateClienteCommand(
                id: $id,
                nombre: $request->nombre,
                apellidos: $request->apellidos,
                dniNie: $request->dniNie,
                email: $request->email,
                telefono: $request->telefono,
                activo: $request->activo
            ));

            $handledStamp = $envelope->last(HandledStamp::class);

            /** @var ClienteResponse $cliente */
            $cliente = $handledStamp->getResult();

            return $this->json($cliente->toArray());
        } catch (ClienteNotFoundException $e) {
            return $this->json([
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => 'CLIENTE_NOT_FOUND',
                ],
            ], Response::HTTP_NOT_FOUND);
        } catch (InvalidDniNieException $e) {
            return $this->json([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => 'VALIDATION_ERROR',
                    'details' => [
                        'dniNie' => [$e->getMessage()],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
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
        } catch (InvalidTelefonoException $e) {
            return $this->json([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => 'VALIDATION_ERROR',
                    'details' => [
                        'telefono' => [$e->getMessage()],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        } catch (DuplicatedDniNieException | DuplicatedEmailException $e) {
            return $this->json([
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => 'CONFLICT',
                ],
            ], Response::HTTP_CONFLICT);
        }
    }

    #[Route('/{id}', name: 'clientes_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id): JsonResponse
    {
        try {
            $this->commandBus->dispatch(new DeleteClienteCommand($id));

            return $this->json(null, Response::HTTP_NO_CONTENT);
        } catch (ClienteNotFoundException $e) {
            return $this->json([
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => 'CLIENTE_NOT_FOUND',
                ],
            ], Response::HTTP_NOT_FOUND);
        }
    }
}
