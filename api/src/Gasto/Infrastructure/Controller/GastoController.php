<?php

declare(strict_types=1);

namespace App\Gasto\Infrastructure\Controller;

use App\Gasto\Application\Command\CreateGasto\CreateGastoCommand;
use App\Gasto\Application\Command\DeleteGasto\DeleteGastoCommand;
use App\Gasto\Application\Command\UpdateGasto\UpdateGastoCommand;
use App\Gasto\Application\DTO\GastoRequest;
use App\Gasto\Application\DTO\GastoResponse;
use App\Gasto\Application\Query\FindGasto\FindGastoQuery;
use App\Gasto\Application\Query\FindGastosByLocal\FindGastosByLocalQuery;
use App\Gasto\Application\Query\ListGastos\ListGastosQuery;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;
use App\Auth\Infrastructure\Attribute\Auth;

#[Route('/api/gastos')]
#[Auth]
#[OA\Tag(name: 'Gastos')]
final class GastoController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly MessageBusInterface $queryBus
    ) {
    }

    #[Route('', name: 'gastos_list', methods: ['GET'])]
    #[OA\Get(summary: 'Listar gastos', description: 'Obtiene la lista de todos los gastos')]
    #[OA\Parameter(name: 'localId', in: 'query', description: 'Filtrar por local', schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'categoria', in: 'query', description: 'Filtrar por categoria', schema: new OA\Schema(type: 'string', enum: ['mantenimiento', 'suministros', 'impuestos', 'seguros', 'otros']))]
    #[OA\Parameter(name: 'desde', in: 'query', description: 'Fecha desde (YYYY-MM-DD)', schema: new OA\Schema(type: 'string', format: 'date'))]
    #[OA\Parameter(name: 'hasta', in: 'query', description: 'Fecha hasta (YYYY-MM-DD)', schema: new OA\Schema(type: 'string', format: 'date'))]
    #[OA\Parameter(name: 'onlyActive', in: 'query', description: 'Solo activos', schema: new OA\Schema(type: 'boolean'))]
    #[OA\Response(
        response: 200,
        description: 'Lista de gastos',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Gasto')),
                new OA\Property(property: 'meta', ref: '#/components/schemas/PaginatedMeta')
            ]
        )
    )]
    #[OA\Response(response: 400, description: 'Error de validacion', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))]
    public function list(Request $request): JsonResponse
    {
        $localId = $request->query->get('localId');
        $categoria = $request->query->get('categoria');
        $desde = $request->query->get('desde');
        $hasta = $request->query->get('hasta');
        $onlyActive = $request->query->get('onlyActive');

        $localIdFilter = $localId !== null ? (int) $localId : null;
        $onlyActiveFilter = $onlyActive !== null
            ? filter_var($onlyActive, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
            : null;

        $envelope = $this->queryBus->dispatch(new ListGastosQuery(
            localId: $localIdFilter,
            categoria: $categoria,
            desde: $desde,
            hasta: $hasta,
            onlyActive: $onlyActiveFilter
        ));

        $handledStamp = $envelope->last(HandledStamp::class);

        /** @var GastoResponse[] $gastos */
        $gastos = $handledStamp->getResult();

        return $this->json([
            'data' => array_map(fn(GastoResponse $gasto) => $gasto->toArray(), $gastos),
            'meta' => [
                'total' => count($gastos),
            ],
        ]);
    }

    #[Route('/{id}', name: 'gastos_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[OA\Get(summary: 'Obtener gasto', description: 'Obtiene los datos de un gasto por su ID')]
    #[OA\Parameter(name: 'id', in: 'path', description: 'ID del gasto', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Gasto encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Gasto'))]
    #[OA\Response(response: 404, description: 'Gasto no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Error'))]
    public function show(int $id): JsonResponse
    {
        $envelope = $this->queryBus->dispatch(new FindGastoQuery($id));
        $handledStamp = $envelope->last(HandledStamp::class);

        /** @var GastoResponse $gasto */
        $gasto = $handledStamp->getResult();

        return $this->json($gasto->toArray());
    }

    #[Route('', name: 'gastos_create', methods: ['POST'])]
    #[OA\Post(summary: 'Crear gasto', description: 'Crea un nuevo gasto')]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['localId', 'concepto', 'importe', 'fecha', 'categoria'],
            properties: [
                new OA\Property(property: 'localId', type: 'integer'),
                new OA\Property(property: 'concepto', type: 'string', example: 'Reparacion puerta'),
                new OA\Property(property: 'importe', type: 'number', format: 'float', example: 150.0),
                new OA\Property(property: 'fecha', type: 'string', format: 'date', example: '2024-01-15'),
                new OA\Property(property: 'categoria', type: 'string', enum: ['mantenimiento', 'suministros', 'impuestos', 'seguros', 'otros']),
                new OA\Property(property: 'descripcion', type: 'string', nullable: true),
                new OA\Property(property: 'metodoPago', type: 'string', enum: ['efectivo', 'transferencia', 'tarjeta', 'bizum'], nullable: true)
            ]
        )
    )]
    #[OA\Response(response: 201, description: 'Gasto creado', content: new OA\JsonContent(ref: '#/components/schemas/Gasto'))]
    #[OA\Response(response: 400, description: 'Error de validacion', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))]
    public function create(#[MapRequestPayload] GastoRequest $request): JsonResponse
    {
        $envelope = $this->commandBus->dispatch(new CreateGastoCommand(
            localId: $request->localId,
            concepto: $request->concepto,
            importe: $request->importe,
            fecha: $request->fecha,
            categoria: $request->categoria,
            descripcion: $request->descripcion,
            metodoPago: $request->metodoPago
        ));

        $handledStamp = $envelope->last(HandledStamp::class);

        /** @var GastoResponse $gasto */
        $gasto = $handledStamp->getResult();

        return $this->json($gasto->toArray(), Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'gastos_update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    #[OA\Put(summary: 'Actualizar gasto', description: 'Actualiza los datos de un gasto')]
    #[OA\Parameter(name: 'id', in: 'path', description: 'ID del gasto', schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['localId', 'concepto', 'importe', 'fecha', 'categoria'],
            properties: [
                new OA\Property(property: 'localId', type: 'integer'),
                new OA\Property(property: 'concepto', type: 'string'),
                new OA\Property(property: 'importe', type: 'number', format: 'float'),
                new OA\Property(property: 'fecha', type: 'string', format: 'date'),
                new OA\Property(property: 'categoria', type: 'string', enum: ['mantenimiento', 'suministros', 'impuestos', 'seguros', 'otros']),
                new OA\Property(property: 'descripcion', type: 'string', nullable: true),
                new OA\Property(property: 'metodoPago', type: 'string', enum: ['efectivo', 'transferencia', 'tarjeta', 'bizum'], nullable: true)
            ]
        )
    )]
    #[OA\Response(response: 200, description: 'Gasto actualizado', content: new OA\JsonContent(ref: '#/components/schemas/Gasto'))]
    #[OA\Response(response: 400, description: 'Error de validacion', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))]
    #[OA\Response(response: 404, description: 'Gasto no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Error'))]
    public function update(int $id, #[MapRequestPayload] GastoRequest $request): JsonResponse
    {
        $envelope = $this->commandBus->dispatch(new UpdateGastoCommand(
            id: $id,
            localId: $request->localId,
            concepto: $request->concepto,
            importe: $request->importe,
            fecha: $request->fecha,
            categoria: $request->categoria,
            descripcion: $request->descripcion,
            metodoPago: $request->metodoPago
        ));

        $handledStamp = $envelope->last(HandledStamp::class);

        /** @var GastoResponse $gasto */
        $gasto = $handledStamp->getResult();

        return $this->json($gasto->toArray());
    }

    #[Route('/{id}', name: 'gastos_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    #[OA\Delete(summary: 'Eliminar gasto', description: 'Elimina un gasto')]
    #[OA\Parameter(name: 'id', in: 'path', description: 'ID del gasto', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 204, description: 'Gasto eliminado')]
    #[OA\Response(response: 404, description: 'Gasto no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Error'))]
    public function delete(int $id): JsonResponse
    {
        $this->commandBus->dispatch(new DeleteGastoCommand($id));

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/local/{localId}', name: 'gastos_by_local', methods: ['GET'], requirements: ['localId' => '\d+'])]
    #[OA\Get(summary: 'Gastos por local', description: 'Obtiene todos los gastos de un local especifico')]
    #[OA\Parameter(name: 'localId', in: 'path', description: 'ID del local', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(
        response: 200,
        description: 'Lista de gastos del local',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Gasto')),
                new OA\Property(property: 'meta', ref: '#/components/schemas/PaginatedMeta')
            ]
        )
    )]
    public function byLocal(int $localId): JsonResponse
    {
        $envelope = $this->queryBus->dispatch(new FindGastosByLocalQuery($localId));
        $handledStamp = $envelope->last(HandledStamp::class);

        /** @var GastoResponse[] $gastos */
        $gastos = $handledStamp->getResult();

        return $this->json([
            'data' => array_map(fn(GastoResponse $gasto) => $gasto->toArray(), $gastos),
            'meta' => [
                'total' => count($gastos),
            ],
        ]);
    }
}
