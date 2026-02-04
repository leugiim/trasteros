<?php

declare(strict_types=1);

namespace App\Ingreso\Infrastructure\Controller;

use App\Ingreso\Application\Command\CreateIngreso\CreateIngresoCommand;
use App\Ingreso\Application\Command\DeleteIngreso\DeleteIngresoCommand;
use App\Ingreso\Application\Command\UpdateIngreso\UpdateIngresoCommand;
use App\Ingreso\Application\DTO\IngresoRequest;
use App\Ingreso\Application\DTO\IngresoResponse;
use App\Ingreso\Application\Query\FindIngreso\FindIngresoQuery;
use App\Ingreso\Application\Query\FindIngresosByContrato\FindIngresosByContratoQuery;
use App\Ingreso\Application\Query\FindIngresosByLocal\FindIngresosByLocalQuery;
use App\Ingreso\Application\Query\FindIngresosByTrastero\FindIngresosByTrasteroQuery;
use App\Ingreso\Application\Query\ListIngresos\ListIngresosQuery;
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

#[Route('/api/ingresos')]
#[Auth]
#[OA\Tag(name: 'Ingresos')]
final class IngresoController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly MessageBusInterface $queryBus
    ) {
    }

    #[Route('', name: 'ingresos_list', methods: ['GET'])]
    #[OA\Get(summary: 'Listar ingresos', description: 'Obtiene la lista de todos los ingresos')]
    #[OA\Parameter(name: 'contratoId', in: 'query', description: 'Filtrar por contrato', schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'categoria', in: 'query', description: 'Filtrar por categoria', schema: new OA\Schema(type: 'string', enum: ['alquiler', 'fianza', 'otros']))]
    #[OA\Parameter(name: 'desde', in: 'query', description: 'Fecha desde (YYYY-MM-DD)', schema: new OA\Schema(type: 'string', format: 'date'))]
    #[OA\Parameter(name: 'hasta', in: 'query', description: 'Fecha hasta (YYYY-MM-DD)', schema: new OA\Schema(type: 'string', format: 'date'))]
    #[OA\Parameter(name: 'onlyActive', in: 'query', description: 'Solo activos', schema: new OA\Schema(type: 'boolean'))]
    #[OA\Response(
        response: 200,
        description: 'Lista de ingresos',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Ingreso')),
                new OA\Property(property: 'meta', ref: '#/components/schemas/PaginatedMeta')
            ]
        )
    )]
    #[OA\Response(response: 400, description: 'Error de validacion', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))]
    public function list(Request $request): JsonResponse
    {
        $contratoId = $request->query->get('contratoId');
        $categoria = $request->query->get('categoria');
        $desde = $request->query->get('desde');
        $hasta = $request->query->get('hasta');
        $onlyActive = $request->query->get('onlyActive');

        $contratoIdFilter = $contratoId !== null ? (int) $contratoId : null;
        $onlyActiveFilter = $onlyActive !== null
            ? filter_var($onlyActive, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
            : null;

        $envelope = $this->queryBus->dispatch(new ListIngresosQuery(
            contratoId: $contratoIdFilter,
            categoria: $categoria,
            desde: $desde,
            hasta: $hasta,
            onlyActive: $onlyActiveFilter
        ));

        $handledStamp = $envelope->last(HandledStamp::class);

        /** @var IngresoResponse[] $ingresos */
        $ingresos = $handledStamp->getResult();

        return $this->json([
            'data' => array_map(fn(IngresoResponse $ingreso) => $ingreso->toArray(), $ingresos),
            'meta' => [
                'total' => count($ingresos),
            ],
        ]);
    }

    #[Route('/{id}', name: 'ingresos_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[OA\Get(summary: 'Obtener ingreso', description: 'Obtiene los datos de un ingreso por su ID')]
    #[OA\Parameter(name: 'id', in: 'path', description: 'ID del ingreso', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Ingreso encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Ingreso'))]
    #[OA\Response(response: 404, description: 'Ingreso no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Error'))]
    public function show(int $id): JsonResponse
    {
        $envelope = $this->queryBus->dispatch(new FindIngresoQuery($id));
        $handledStamp = $envelope->last(HandledStamp::class);

        /** @var IngresoResponse $ingreso */
        $ingreso = $handledStamp->getResult();

        return $this->json($ingreso->toArray());
    }

    #[Route('', name: 'ingresos_create', methods: ['POST'])]
    #[OA\Post(summary: 'Crear ingreso', description: 'Crea un nuevo ingreso')]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['contratoId', 'concepto', 'importe', 'fechaPago', 'categoria'],
            properties: [
                new OA\Property(property: 'contratoId', type: 'integer'),
                new OA\Property(property: 'concepto', type: 'string', example: 'Alquiler mensual'),
                new OA\Property(property: 'importe', type: 'number', format: 'float', example: 50.0),
                new OA\Property(property: 'fechaPago', type: 'string', format: 'date', example: '2024-01-15'),
                new OA\Property(property: 'categoria', type: 'string', enum: ['alquiler', 'fianza', 'otros']),
                new OA\Property(property: 'metodoPago', type: 'string', enum: ['efectivo', 'transferencia', 'tarjeta', 'bizum'], nullable: true)
            ]
        )
    )]
    #[OA\Response(response: 201, description: 'Ingreso creado', content: new OA\JsonContent(ref: '#/components/schemas/Ingreso'))]
    #[OA\Response(response: 400, description: 'Error de validacion', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))]
    public function create(#[MapRequestPayload] IngresoRequest $request): JsonResponse
    {
        $envelope = $this->commandBus->dispatch(new CreateIngresoCommand(
            contratoId: $request->contratoId,
            concepto: $request->concepto,
            importe: $request->importe,
            fechaPago: $request->fechaPago,
            categoria: $request->categoria,
            metodoPago: $request->metodoPago
        ));

        $handledStamp = $envelope->last(HandledStamp::class);

        /** @var IngresoResponse $ingreso */
        $ingreso = $handledStamp->getResult();

        return $this->json($ingreso->toArray(), Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'ingresos_update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    #[OA\Put(summary: 'Actualizar ingreso', description: 'Actualiza los datos de un ingreso')]
    #[OA\Parameter(name: 'id', in: 'path', description: 'ID del ingreso', schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['contratoId', 'concepto', 'importe', 'fechaPago', 'categoria'],
            properties: [
                new OA\Property(property: 'contratoId', type: 'integer'),
                new OA\Property(property: 'concepto', type: 'string'),
                new OA\Property(property: 'importe', type: 'number', format: 'float'),
                new OA\Property(property: 'fechaPago', type: 'string', format: 'date'),
                new OA\Property(property: 'categoria', type: 'string', enum: ['alquiler', 'fianza', 'otros']),
                new OA\Property(property: 'metodoPago', type: 'string', enum: ['efectivo', 'transferencia', 'tarjeta', 'bizum'], nullable: true)
            ]
        )
    )]
    #[OA\Response(response: 200, description: 'Ingreso actualizado', content: new OA\JsonContent(ref: '#/components/schemas/Ingreso'))]
    #[OA\Response(response: 400, description: 'Error de validacion', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))]
    #[OA\Response(response: 404, description: 'Ingreso no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Error'))]
    public function update(int $id, #[MapRequestPayload] IngresoRequest $request): JsonResponse
    {
        $envelope = $this->commandBus->dispatch(new UpdateIngresoCommand(
            id: $id,
            contratoId: $request->contratoId,
            concepto: $request->concepto,
            importe: $request->importe,
            fechaPago: $request->fechaPago,
            categoria: $request->categoria,
            metodoPago: $request->metodoPago
        ));

        $handledStamp = $envelope->last(HandledStamp::class);

        /** @var IngresoResponse $ingreso */
        $ingreso = $handledStamp->getResult();

        return $this->json($ingreso->toArray());
    }

    #[Route('/{id}', name: 'ingresos_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    #[OA\Delete(summary: 'Eliminar ingreso', description: 'Elimina un ingreso')]
    #[OA\Parameter(name: 'id', in: 'path', description: 'ID del ingreso', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 204, description: 'Ingreso eliminado')]
    #[OA\Response(response: 404, description: 'Ingreso no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Error'))]
    public function delete(int $id): JsonResponse
    {
        $this->commandBus->dispatch(new DeleteIngresoCommand($id));

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/contrato/{contratoId}', name: 'ingresos_by_contrato', methods: ['GET'], requirements: ['contratoId' => '\d+'])]
    #[OA\Get(summary: 'Ingresos por contrato', description: 'Obtiene todos los ingresos de un contrato especifico')]
    #[OA\Parameter(name: 'contratoId', in: 'path', description: 'ID del contrato', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(
        response: 200,
        description: 'Lista de ingresos del contrato',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Ingreso')),
                new OA\Property(property: 'meta', ref: '#/components/schemas/PaginatedMeta')
            ]
        )
    )]
    public function byContrato(int $contratoId): JsonResponse
    {
        $envelope = $this->queryBus->dispatch(new FindIngresosByContratoQuery($contratoId));
        $handledStamp = $envelope->last(HandledStamp::class);

        /** @var IngresoResponse[] $ingresos */
        $ingresos = $handledStamp->getResult();

        return $this->json([
            'data' => array_map(fn(IngresoResponse $ingreso) => $ingreso->toArray(), $ingresos),
            'meta' => [
                'total' => count($ingresos),
            ],
        ]);
    }

    #[Route('/trastero/{trasteroId}', name: 'ingresos_by_trastero', methods: ['GET'], requirements: ['trasteroId' => '\d+'])]
    #[OA\Get(summary: 'Ingresos por trastero', description: 'Obtiene todos los ingresos de un trastero especifico')]
    #[OA\Parameter(name: 'trasteroId', in: 'path', description: 'ID del trastero', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(
        response: 200,
        description: 'Lista de ingresos del trastero',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Ingreso')),
                new OA\Property(property: 'meta', ref: '#/components/schemas/PaginatedMeta')
            ]
        )
    )]
    public function byTrastero(int $trasteroId): JsonResponse
    {
        $envelope = $this->queryBus->dispatch(new FindIngresosByTrasteroQuery($trasteroId));
        $handledStamp = $envelope->last(HandledStamp::class);

        /** @var IngresoResponse[] $ingresos */
        $ingresos = $handledStamp->getResult();

        return $this->json([
            'data' => array_map(fn(IngresoResponse $ingreso) => $ingreso->toArray(), $ingresos),
            'meta' => [
                'total' => count($ingresos),
            ],
        ]);
    }

    #[Route('/local/{localId}', name: 'ingresos_by_local', methods: ['GET'], requirements: ['localId' => '\d+'])]
    #[OA\Get(summary: 'Ingresos por local', description: 'Obtiene todos los ingresos de un local especifico')]
    #[OA\Parameter(name: 'localId', in: 'path', description: 'ID del local', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(
        response: 200,
        description: 'Lista de ingresos del local',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Ingreso')),
                new OA\Property(property: 'meta', ref: '#/components/schemas/PaginatedMeta')
            ]
        )
    )]
    public function byLocal(int $localId): JsonResponse
    {
        $envelope = $this->queryBus->dispatch(new FindIngresosByLocalQuery($localId));
        $handledStamp = $envelope->last(HandledStamp::class);

        /** @var IngresoResponse[] $ingresos */
        $ingresos = $handledStamp->getResult();

        return $this->json([
            'data' => array_map(fn(IngresoResponse $ingreso) => $ingreso->toArray(), $ingresos),
            'meta' => [
                'total' => count($ingresos),
            ],
        ]);
    }
}
