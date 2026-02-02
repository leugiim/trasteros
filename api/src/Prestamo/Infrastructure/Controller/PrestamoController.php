<?php

declare(strict_types=1);

namespace App\Prestamo\Infrastructure\Controller;

use App\Local\Domain\Exception\LocalNotFoundException;
use App\Prestamo\Application\Command\CreatePrestamo\CreatePrestamoCommand;
use App\Prestamo\Application\Command\DeletePrestamo\DeletePrestamoCommand;
use App\Prestamo\Application\Command\UpdatePrestamo\UpdatePrestamoCommand;
use App\Prestamo\Application\DTO\PrestamoRequest;
use App\Prestamo\Application\DTO\PrestamoResponse;
use App\Prestamo\Application\Query\FindPrestamo\FindPrestamoQuery;
use App\Prestamo\Application\Query\FindPrestamosByLocal\FindPrestamosByLocalQuery;
use App\Prestamo\Application\Query\ListPrestamos\ListPrestamosQuery;
use App\Prestamo\Domain\Exception\InvalidCapitalSolicitadoException;
use App\Prestamo\Domain\Exception\InvalidPrestamoEstadoException;
use App\Prestamo\Domain\Exception\InvalidTipoInteresException;
use App\Prestamo\Domain\Exception\InvalidTotalADevolverException;
use App\Prestamo\Domain\Exception\PrestamoNotFoundException;
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

#[Route('/api/prestamos')]
#[Auth]
#[OA\Tag(name: 'Prestamos')]
final class PrestamoController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly MessageBusInterface $queryBus
    ) {
    }

    #[Route('', name: 'prestamos_list', methods: ['GET'])]
    #[OA\Get(summary: 'Listar prestamos', description: 'Obtiene la lista de todos los prestamos')]
    #[OA\Parameter(name: 'localId', in: 'query', description: 'Filtrar por local', schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'estado', in: 'query', description: 'Filtrar por estado', schema: new OA\Schema(type: 'string', enum: ['activo', 'pagado', 'cancelado']))]
    #[OA\Parameter(name: 'entidadBancaria', in: 'query', description: 'Filtrar por entidad bancaria', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'onlyActive', in: 'query', description: 'Solo activos', schema: new OA\Schema(type: 'boolean'))]
    #[OA\Response(
        response: 200,
        description: 'Lista de prestamos',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Prestamo')),
                new OA\Property(property: 'meta', ref: '#/components/schemas/PaginatedMeta')
            ]
        )
    )]
    #[OA\Response(response: 400, description: 'Error de validacion', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))]
    public function list(Request $request): JsonResponse
    {
        $localId = $request->query->get('localId');
        $estado = $request->query->get('estado');
        $entidadBancaria = $request->query->get('entidadBancaria');
        $onlyActive = $request->query->get('onlyActive');

        $localIdFilter = $localId !== null ? (int) $localId : null;
        $onlyActiveFilter = $onlyActive !== null
            ? filter_var($onlyActive, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
            : null;

        try {
            $envelope = $this->queryBus->dispatch(new ListPrestamosQuery(
                localId: $localIdFilter,
                estado: $estado,
                entidadBancaria: $entidadBancaria,
                onlyActive: $onlyActiveFilter
            ));

            $handledStamp = $envelope->last(HandledStamp::class);

            /** @var PrestamoResponse[] $prestamos */
            $prestamos = $handledStamp->getResult();

            return $this->json([
                'data' => array_map(fn(PrestamoResponse $prestamo) => $prestamo->toArray(), $prestamos),
                'meta' => [
                    'total' => count($prestamos),
                ],
            ]);
        } catch (InvalidPrestamoEstadoException $e) {
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

    #[Route('/{id}', name: 'prestamos_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[OA\Get(summary: 'Obtener prestamo', description: 'Obtiene los datos de un prestamo por su ID')]
    #[OA\Parameter(name: 'id', in: 'path', description: 'ID del prestamo', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Prestamo encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Prestamo'))]
    #[OA\Response(response: 404, description: 'Prestamo no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Error'))]
    public function show(int $id): JsonResponse
    {
        try {
            $envelope = $this->queryBus->dispatch(new FindPrestamoQuery($id));
            $handledStamp = $envelope->last(HandledStamp::class);

            /** @var PrestamoResponse $prestamo */
            $prestamo = $handledStamp->getResult();

            return $this->json($prestamo->toArray());
        } catch (PrestamoNotFoundException $e) {
            return $this->json([
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => 'PRESTAMO_NOT_FOUND',
                ],
            ], Response::HTTP_NOT_FOUND);
        }
    }

    #[Route('', name: 'prestamos_create', methods: ['POST'])]
    #[OA\Post(summary: 'Crear prestamo', description: 'Crea un nuevo prestamo')]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['localId', 'capitalSolicitado', 'totalADevolver', 'fechaConcesion', 'entidadBancaria'],
            properties: [
                new OA\Property(property: 'localId', type: 'integer'),
                new OA\Property(property: 'capitalSolicitado', type: 'number', format: 'float', example: 50000.0),
                new OA\Property(property: 'totalADevolver', type: 'number', format: 'float', example: 55000.0),
                new OA\Property(property: 'fechaConcesion', type: 'string', format: 'date', example: '2024-01-01'),
                new OA\Property(property: 'entidadBancaria', type: 'string', example: 'Banco Santander'),
                new OA\Property(property: 'numeroPrestamo', type: 'string', nullable: true, example: 'PREST-001'),
                new OA\Property(property: 'tipoInteres', type: 'number', format: 'float', nullable: true, example: 3.5),
                new OA\Property(property: 'estado', type: 'string', enum: ['activo', 'pagado', 'cancelado'], default: 'activo')
            ]
        )
    )]
    #[OA\Response(response: 201, description: 'Prestamo creado', content: new OA\JsonContent(ref: '#/components/schemas/Prestamo'))]
    #[OA\Response(response: 400, description: 'Error de validacion', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))]
    public function create(#[MapRequestPayload] PrestamoRequest $request): JsonResponse
    {
        try {
            $envelope = $this->commandBus->dispatch(new CreatePrestamoCommand(
                localId: $request->localId,
                capitalSolicitado: $request->capitalSolicitado,
                totalADevolver: $request->totalADevolver,
                fechaConcesion: $request->fechaConcesion,
                entidadBancaria: $request->entidadBancaria,
                numeroPrestamo: $request->numeroPrestamo,
                tipoInteres: $request->tipoInteres,
                estado: $request->estado
            ));

            $handledStamp = $envelope->last(HandledStamp::class);

            /** @var PrestamoResponse $prestamo */
            $prestamo = $handledStamp->getResult();

            return $this->json($prestamo->toArray(), Response::HTTP_CREATED);
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
        } catch (InvalidPrestamoEstadoException $e) {
            return $this->json([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => 'VALIDATION_ERROR',
                    'details' => [
                        'estado' => [$e->getMessage()],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        } catch (InvalidCapitalSolicitadoException $e) {
            return $this->json([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => 'VALIDATION_ERROR',
                    'details' => [
                        'capitalSolicitado' => [$e->getMessage()],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        } catch (InvalidTotalADevolverException $e) {
            return $this->json([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => 'VALIDATION_ERROR',
                    'details' => [
                        'totalADevolver' => [$e->getMessage()],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        } catch (InvalidTipoInteresException $e) {
            return $this->json([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => 'VALIDATION_ERROR',
                    'details' => [
                        'tipoInteres' => [$e->getMessage()],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => 'VALIDATION_ERROR',
                    'details' => [
                        'fechaConcesion' => [$e->getMessage()],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'prestamos_update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    #[OA\Put(summary: 'Actualizar prestamo', description: 'Actualiza los datos de un prestamo')]
    #[OA\Parameter(name: 'id', in: 'path', description: 'ID del prestamo', schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['localId', 'capitalSolicitado', 'totalADevolver', 'fechaConcesion', 'entidadBancaria'],
            properties: [
                new OA\Property(property: 'localId', type: 'integer'),
                new OA\Property(property: 'capitalSolicitado', type: 'number', format: 'float'),
                new OA\Property(property: 'totalADevolver', type: 'number', format: 'float'),
                new OA\Property(property: 'fechaConcesion', type: 'string', format: 'date'),
                new OA\Property(property: 'entidadBancaria', type: 'string'),
                new OA\Property(property: 'numeroPrestamo', type: 'string', nullable: true),
                new OA\Property(property: 'tipoInteres', type: 'number', format: 'float', nullable: true),
                new OA\Property(property: 'estado', type: 'string', enum: ['activo', 'pagado', 'cancelado'])
            ]
        )
    )]
    #[OA\Response(response: 200, description: 'Prestamo actualizado', content: new OA\JsonContent(ref: '#/components/schemas/Prestamo'))]
    #[OA\Response(response: 400, description: 'Error de validacion', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))]
    #[OA\Response(response: 404, description: 'Prestamo no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Error'))]
    public function update(int $id, #[MapRequestPayload] PrestamoRequest $request): JsonResponse
    {
        try {
            $envelope = $this->commandBus->dispatch(new UpdatePrestamoCommand(
                id: $id,
                localId: $request->localId,
                capitalSolicitado: $request->capitalSolicitado,
                totalADevolver: $request->totalADevolver,
                fechaConcesion: $request->fechaConcesion,
                entidadBancaria: $request->entidadBancaria,
                numeroPrestamo: $request->numeroPrestamo,
                tipoInteres: $request->tipoInteres,
                estado: $request->estado
            ));

            $handledStamp = $envelope->last(HandledStamp::class);

            /** @var PrestamoResponse $prestamo */
            $prestamo = $handledStamp->getResult();

            return $this->json($prestamo->toArray());
        } catch (PrestamoNotFoundException $e) {
            return $this->json([
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => 'PRESTAMO_NOT_FOUND',
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
        } catch (InvalidPrestamoEstadoException $e) {
            return $this->json([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => 'VALIDATION_ERROR',
                    'details' => [
                        'estado' => [$e->getMessage()],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        } catch (InvalidCapitalSolicitadoException $e) {
            return $this->json([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => 'VALIDATION_ERROR',
                    'details' => [
                        'capitalSolicitado' => [$e->getMessage()],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        } catch (InvalidTotalADevolverException $e) {
            return $this->json([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => 'VALIDATION_ERROR',
                    'details' => [
                        'totalADevolver' => [$e->getMessage()],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        } catch (InvalidTipoInteresException $e) {
            return $this->json([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => 'VALIDATION_ERROR',
                    'details' => [
                        'tipoInteres' => [$e->getMessage()],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => 'VALIDATION_ERROR',
                    'details' => [
                        'fechaConcesion' => [$e->getMessage()],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'prestamos_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    #[OA\Delete(summary: 'Eliminar prestamo', description: 'Elimina un prestamo')]
    #[OA\Parameter(name: 'id', in: 'path', description: 'ID del prestamo', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 204, description: 'Prestamo eliminado')]
    #[OA\Response(response: 404, description: 'Prestamo no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Error'))]
    public function delete(int $id): JsonResponse
    {
        try {
            $this->commandBus->dispatch(new DeletePrestamoCommand($id));

            return $this->json(null, Response::HTTP_NO_CONTENT);
        } catch (PrestamoNotFoundException $e) {
            return $this->json([
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => 'PRESTAMO_NOT_FOUND',
                ],
            ], Response::HTTP_NOT_FOUND);
        }
    }

    #[Route('/local/{localId}', name: 'prestamos_by_local', methods: ['GET'], requirements: ['localId' => '\d+'])]
    #[OA\Get(summary: 'Prestamos por local', description: 'Obtiene todos los prestamos de un local especifico')]
    #[OA\Parameter(name: 'localId', in: 'path', description: 'ID del local', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(
        response: 200,
        description: 'Lista de prestamos del local',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Prestamo')),
                new OA\Property(property: 'meta', ref: '#/components/schemas/PaginatedMeta')
            ]
        )
    )]
    public function byLocal(int $localId): JsonResponse
    {
        $envelope = $this->queryBus->dispatch(new FindPrestamosByLocalQuery($localId));
        $handledStamp = $envelope->last(HandledStamp::class);

        /** @var PrestamoResponse[] $prestamos */
        $prestamos = $handledStamp->getResult();

        return $this->json([
            'data' => array_map(fn(PrestamoResponse $prestamo) => $prestamo->toArray(), $prestamos),
            'meta' => [
                'total' => count($prestamos),
            ],
        ]);
    }
}
