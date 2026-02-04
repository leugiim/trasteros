<?php

declare(strict_types=1);

namespace App\Cliente\Infrastructure\Controller;

use App\Auth\Infrastructure\Attribute\Auth;
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
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/clientes')]
#[Auth]
#[OA\Tag(name: 'Clientes')]
final class ClienteController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly MessageBusInterface $queryBus
    ) {
    }

    #[Route('', name: 'clientes_list', methods: ['GET'])]
    #[OA\Get(summary: 'Listar clientes', description: 'Obtiene la lista de todos los clientes')]
    #[OA\Parameter(name: 'search', in: 'query', description: 'Buscar por nombre o apellidos', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'onlyActivos', in: 'query', description: 'Solo clientes activos', schema: new OA\Schema(type: 'boolean'))]
    #[OA\Response(
        response: 200,
        description: 'Lista de clientes',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Cliente')),
                new OA\Property(property: 'meta', ref: '#/components/schemas/PaginatedMeta')
            ]
        )
    )]
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
    #[OA\Get(summary: 'Obtener cliente', description: 'Obtiene los datos de un cliente por su ID')]
    #[OA\Parameter(name: 'id', in: 'path', description: 'ID del cliente', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Cliente encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Cliente'))]
    #[OA\Response(response: 404, description: 'Cliente no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Error'))]
    public function show(int $id): JsonResponse
    {
        $envelope = $this->queryBus->dispatch(new FindClienteQuery($id));
        $handledStamp = $envelope->last(HandledStamp::class);

        /** @var ClienteResponse $cliente */
        $cliente = $handledStamp->getResult();

        return $this->json($cliente->toArray());
    }

    #[Route('', name: 'clientes_create', methods: ['POST'])]
    #[OA\Post(summary: 'Crear cliente', description: 'Crea un nuevo cliente')]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['nombre', 'apellidos', 'dniNie', 'email', 'telefono'],
            properties: [
                new OA\Property(property: 'nombre', type: 'string', example: 'Juan'),
                new OA\Property(property: 'apellidos', type: 'string', example: 'García López'),
                new OA\Property(property: 'dniNie', type: 'string', example: '12345678A'),
                new OA\Property(property: 'email', type: 'string', format: 'email'),
                new OA\Property(property: 'telefono', type: 'string', example: '+34612345678'),
                new OA\Property(property: 'activo', type: 'boolean', default: true)
            ]
        )
    )]
    #[OA\Response(response: 201, description: 'Cliente creado', content: new OA\JsonContent(ref: '#/components/schemas/Cliente'))]
    #[OA\Response(response: 400, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))]
    #[OA\Response(response: 409, description: 'DNI/NIE o email duplicado', content: new OA\JsonContent(ref: '#/components/schemas/Error'))]
    public function create(#[MapRequestPayload] ClienteRequest $request): JsonResponse
    {
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
    }

    #[Route('/{id}', name: 'clientes_update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    #[OA\Put(summary: 'Actualizar cliente', description: 'Actualiza los datos de un cliente')]
    #[OA\Parameter(name: 'id', in: 'path', description: 'ID del cliente', schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['nombre', 'apellidos', 'dniNie', 'email', 'telefono'],
            properties: [
                new OA\Property(property: 'nombre', type: 'string'),
                new OA\Property(property: 'apellidos', type: 'string'),
                new OA\Property(property: 'dniNie', type: 'string'),
                new OA\Property(property: 'email', type: 'string', format: 'email'),
                new OA\Property(property: 'telefono', type: 'string'),
                new OA\Property(property: 'activo', type: 'boolean')
            ]
        )
    )]
    #[OA\Response(response: 200, description: 'Cliente actualizado', content: new OA\JsonContent(ref: '#/components/schemas/Cliente'))]
    #[OA\Response(response: 400, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))]
    #[OA\Response(response: 404, description: 'Cliente no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Error'))]
    #[OA\Response(response: 409, description: 'DNI/NIE o email duplicado', content: new OA\JsonContent(ref: '#/components/schemas/Error'))]
    public function update(int $id, #[MapRequestPayload] ClienteRequest $request): JsonResponse
    {
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
    }

    #[Route('/{id}', name: 'clientes_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    #[OA\Delete(summary: 'Eliminar cliente', description: 'Elimina un cliente')]
    #[OA\Parameter(name: 'id', in: 'path', description: 'ID del cliente', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 204, description: 'Cliente eliminado')]
    #[OA\Response(response: 404, description: 'Cliente no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Error'))]
    public function delete(int $id): JsonResponse
    {
        $this->commandBus->dispatch(new DeleteClienteCommand($id));

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
