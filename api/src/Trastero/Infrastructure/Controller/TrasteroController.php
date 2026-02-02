<?php

declare(strict_types=1);

namespace App\Trastero\Infrastructure\Controller;

use App\Auth\Infrastructure\Attribute\Auth;
use App\Local\Domain\Exception\LocalNotFoundException;
use App\Trastero\Application\Command\CreateTrastero\CreateTrasteroCommand;
use App\Trastero\Application\Command\DeleteTrastero\DeleteTrasteroCommand;
use App\Trastero\Application\Command\UpdateTrastero\UpdateTrasteroCommand;
use App\Trastero\Application\DTO\TrasteroRequest;
use App\Trastero\Application\DTO\TrasteroResponse;
use App\Trastero\Application\Query\FindTrastero\FindTrasteroQuery;
use App\Trastero\Application\Query\FindTrasterosByLocal\FindTrasterosByLocalQuery;
use App\Trastero\Application\Query\FindTrasterosDisponibles\FindTrasterosDisponiblesQuery;
use App\Trastero\Application\Query\ListTrasteros\ListTrasterosQuery;
use App\Trastero\Domain\Exception\DuplicateTrasteroException;
use App\Trastero\Domain\Exception\InvalidPrecioMensualException;
use App\Trastero\Domain\Exception\InvalidSuperficieException;
use App\Trastero\Domain\Exception\InvalidTrasteroEstadoException;
use App\Trastero\Domain\Exception\TrasteroNotFoundException;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/trasteros')]
#[Auth]
#[OA\Tag(name: 'Trasteros')]
final class TrasteroController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly MessageBusInterface $queryBus
    ) {
    }

    #[Route('', name: 'trasteros_list', methods: ['GET'])]
    #[OA\Get(summary: 'Listar trasteros', description: 'Obtiene la lista de todos los trasteros')]
    #[OA\Parameter(name: 'localId', in: 'query', description: 'Filtrar por local', schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'estado', in: 'query', description: 'Filtrar por estado', schema: new OA\Schema(type: 'string', enum: ['disponible', 'ocupado', 'reservado', 'mantenimiento']))]
    #[OA\Parameter(name: 'onlyActive', in: 'query', description: 'Solo activos', schema: new OA\Schema(type: 'boolean'))]
    #[OA\Response(
        response: 200,
        description: 'Lista de trasteros',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Trastero')),
                new OA\Property(property: 'meta', ref: '#/components/schemas/PaginatedMeta')
            ]
        )
    )]
    public function list(Request $request): JsonResponse
    {
        $localId = $request->query->get('localId');
        $estado = $request->query->get('estado');
        $onlyActive = $request->query->get('onlyActive');

        $localIdFilter = $localId !== null ? (int) $localId : null;
        $onlyActiveFilter = $onlyActive !== null
            ? filter_var($onlyActive, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
            : null;

        try {
            $envelope = $this->queryBus->dispatch(new ListTrasterosQuery(
                localId: $localIdFilter,
                estado: $estado,
                onlyActive: $onlyActiveFilter
            ));

            $handledStamp = $envelope->last(HandledStamp::class);

            /** @var TrasteroResponse[] $trasteros */
            $trasteros = $handledStamp->getResult();

            return $this->json([
                'data' => array_map(fn(TrasteroResponse $trastero) => $trastero->toArray(), $trasteros),
                'meta' => [
                    'total' => count($trasteros),
                ],
            ]);
        } catch (InvalidTrasteroEstadoException $e) {
            return $this->json([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => 'VALIDATION_ERROR',
                    'details' => [
                        'estado' => [$e->getMessage()],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'trasteros_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[OA\Get(summary: 'Obtener trastero', description: 'Obtiene los datos de un trastero por su ID')]
    #[OA\Parameter(name: 'id', in: 'path', description: 'ID del trastero', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Trastero encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Trastero'))]
    #[OA\Response(response: 404, description: 'Trastero no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Error'))]
    public function show(int $id): JsonResponse
    {
        try {
            $envelope = $this->queryBus->dispatch(new FindTrasteroQuery($id));
            $handledStamp = $envelope->last(HandledStamp::class);

            /** @var TrasteroResponse $trastero */
            $trastero = $handledStamp->getResult();

            return $this->json($trastero->toArray());
        } catch (TrasteroNotFoundException $e) {
            return $this->json([
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => 'TRASTERO_NOT_FOUND',
                ],
            ], Response::HTTP_NOT_FOUND);
        }
    }

    #[Route('', name: 'trasteros_create', methods: ['POST'])]
    #[OA\Post(summary: 'Crear trastero', description: 'Crea un nuevo trastero')]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['localId', 'numero', 'superficie', 'precioMensual'],
            properties: [
                new OA\Property(property: 'localId', type: 'integer'),
                new OA\Property(property: 'numero', type: 'string', example: 'A-01'),
                new OA\Property(property: 'nombre', type: 'string', nullable: true),
                new OA\Property(property: 'superficie', type: 'number', format: 'float', example: 5.5),
                new OA\Property(property: 'precioMensual', type: 'number', format: 'float', example: 50.0),
                new OA\Property(property: 'estado', type: 'string', enum: ['disponible', 'ocupado', 'reservado', 'mantenimiento'], default: 'disponible')
            ]
        )
    )]
    #[OA\Response(response: 201, description: 'Trastero creado', content: new OA\JsonContent(ref: '#/components/schemas/Trastero'))]
    #[OA\Response(response: 400, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))]
    #[OA\Response(response: 409, description: 'Trastero duplicado', content: new OA\JsonContent(ref: '#/components/schemas/Error'))]
    public function create(#[MapRequestPayload] TrasteroRequest $request): JsonResponse
    {
        try {
            $envelope = $this->commandBus->dispatch(new CreateTrasteroCommand(
                localId: $request->localId,
                numero: $request->numero,
                superficie: $request->superficie,
                precioMensual: $request->precioMensual,
                nombre: $request->nombre,
                estado: $request->estado
            ));

            $handledStamp = $envelope->last(HandledStamp::class);

            /** @var TrasteroResponse $trastero */
            $trastero = $handledStamp->getResult();

            return $this->json($trastero->toArray(), Response::HTTP_CREATED);
        } catch (LocalNotFoundException $e) {
            return $this->json([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => 'VALIDATION_ERROR',
                    'details' => [
                        'localId' => [$e->getMessage()],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        } catch (DuplicateTrasteroException $e) {
            return $this->json([
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => 'DUPLICATE_TRASTERO',
                ],
            ], Response::HTTP_CONFLICT);
        } catch (InvalidTrasteroEstadoException $e) {
            return $this->json([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => 'VALIDATION_ERROR',
                    'details' => [
                        'estado' => [$e->getMessage()],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        } catch (InvalidSuperficieException $e) {
            return $this->json([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => 'VALIDATION_ERROR',
                    'details' => [
                        'superficie' => [$e->getMessage()],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        } catch (InvalidPrecioMensualException $e) {
            return $this->json([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => 'VALIDATION_ERROR',
                    'details' => [
                        'precioMensual' => [$e->getMessage()],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'trasteros_update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    #[OA\Put(summary: 'Actualizar trastero', description: 'Actualiza los datos de un trastero')]
    #[OA\Parameter(name: 'id', in: 'path', description: 'ID del trastero', schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['localId', 'numero', 'superficie', 'precioMensual'],
            properties: [
                new OA\Property(property: 'localId', type: 'integer'),
                new OA\Property(property: 'numero', type: 'string'),
                new OA\Property(property: 'nombre', type: 'string', nullable: true),
                new OA\Property(property: 'superficie', type: 'number', format: 'float'),
                new OA\Property(property: 'precioMensual', type: 'number', format: 'float'),
                new OA\Property(property: 'estado', type: 'string', enum: ['disponible', 'ocupado', 'reservado', 'mantenimiento'])
            ]
        )
    )]
    #[OA\Response(response: 200, description: 'Trastero actualizado', content: new OA\JsonContent(ref: '#/components/schemas/Trastero'))]
    #[OA\Response(response: 400, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))]
    #[OA\Response(response: 404, description: 'Trastero no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Error'))]
    #[OA\Response(response: 409, description: 'Trastero duplicado', content: new OA\JsonContent(ref: '#/components/schemas/Error'))]
    public function update(int $id, #[MapRequestPayload] TrasteroRequest $request): JsonResponse
    {
        try {
            $envelope = $this->commandBus->dispatch(new UpdateTrasteroCommand(
                id: $id,
                localId: $request->localId,
                numero: $request->numero,
                superficie: $request->superficie,
                precioMensual: $request->precioMensual,
                nombre: $request->nombre,
                estado: $request->estado
            ));

            $handledStamp = $envelope->last(HandledStamp::class);

            /** @var TrasteroResponse $trastero */
            $trastero = $handledStamp->getResult();

            return $this->json($trastero->toArray());
        } catch (TrasteroNotFoundException $e) {
            return $this->json([
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => 'TRASTERO_NOT_FOUND',
                ],
            ], Response::HTTP_NOT_FOUND);
        } catch (LocalNotFoundException $e) {
            return $this->json([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => 'VALIDATION_ERROR',
                    'details' => [
                        'localId' => [$e->getMessage()],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        } catch (DuplicateTrasteroException $e) {
            return $this->json([
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => 'DUPLICATE_TRASTERO',
                ],
            ], Response::HTTP_CONFLICT);
        } catch (InvalidTrasteroEstadoException $e) {
            return $this->json([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => 'VALIDATION_ERROR',
                    'details' => [
                        'estado' => [$e->getMessage()],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        } catch (InvalidSuperficieException $e) {
            return $this->json([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => 'VALIDATION_ERROR',
                    'details' => [
                        'superficie' => [$e->getMessage()],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        } catch (InvalidPrecioMensualException $e) {
            return $this->json([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => 'VALIDATION_ERROR',
                    'details' => [
                        'precioMensual' => [$e->getMessage()],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'trasteros_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    #[OA\Delete(summary: 'Eliminar trastero', description: 'Elimina un trastero')]
    #[OA\Parameter(name: 'id', in: 'path', description: 'ID del trastero', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 204, description: 'Trastero eliminado')]
    #[OA\Response(response: 404, description: 'Trastero no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Error'))]
    public function delete(int $id): JsonResponse
    {
        try {
            $this->commandBus->dispatch(new DeleteTrasteroCommand($id));

            return $this->json(null, Response::HTTP_NO_CONTENT);
        } catch (TrasteroNotFoundException $e) {
            return $this->json([
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => 'TRASTERO_NOT_FOUND',
                ],
            ], Response::HTTP_NOT_FOUND);
        }
    }

    #[Route('/local/{localId}', name: 'trasteros_by_local', methods: ['GET'], requirements: ['localId' => '\d+'])]
    #[OA\Get(summary: 'Trasteros por local', description: 'Obtiene todos los trasteros de un local específico')]
    #[OA\Parameter(name: 'localId', in: 'path', description: 'ID del local', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(
        response: 200,
        description: 'Lista de trasteros del local',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Trastero')),
                new OA\Property(property: 'meta', ref: '#/components/schemas/PaginatedMeta')
            ]
        )
    )]
    public function byLocal(int $localId): JsonResponse
    {
        $envelope = $this->queryBus->dispatch(new FindTrasterosByLocalQuery($localId));
        $handledStamp = $envelope->last(HandledStamp::class);

        /** @var TrasteroResponse[] $trasteros */
        $trasteros = $handledStamp->getResult();

        return $this->json([
            'data' => array_map(fn(TrasteroResponse $trastero) => $trastero->toArray(), $trasteros),
            'meta' => [
                'total' => count($trasteros),
            ],
        ]);
    }

    #[Route('/disponibles', name: 'trasteros_disponibles', methods: ['GET'])]
    #[OA\Get(summary: 'Trasteros disponibles', description: 'Obtiene todos los trasteros disponibles para alquilar')]
    #[OA\Response(
        response: 200,
        description: 'Lista de trasteros disponibles',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Trastero')),
                new OA\Property(property: 'meta', ref: '#/components/schemas/PaginatedMeta')
            ]
        )
    )]
    public function disponibles(): JsonResponse
    {
        $envelope = $this->queryBus->dispatch(new FindTrasterosDisponiblesQuery());
        $handledStamp = $envelope->last(HandledStamp::class);

        /** @var TrasteroResponse[] $trasteros */
        $trasteros = $handledStamp->getResult();

        return $this->json([
            'data' => array_map(fn(TrasteroResponse $trastero) => $trastero->toArray(), $trasteros),
            'meta' => [
                'total' => count($trasteros),
            ],
        ]);
    }
}
