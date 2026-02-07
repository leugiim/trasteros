<?php

declare(strict_types=1);

namespace App\Trastero\Infrastructure\Controller;

use App\Auth\Infrastructure\Attribute\Auth;
use App\Contrato\Domain\Repository\ContratoRepositoryInterface;
use App\Trastero\Application\Command\CreateTrastero\CreateTrasteroCommand;
use App\Trastero\Application\Command\DeleteTrastero\DeleteTrasteroCommand;
use App\Trastero\Application\Command\UpdateTrastero\UpdateTrasteroCommand;
use App\Trastero\Application\DTO\TrasteroRequest;
use App\Trastero\Application\DTO\TrasteroResponse;
use App\Trastero\Application\Query\FindTrastero\FindTrasteroQuery;
use App\Trastero\Application\Query\FindTrasterosByLocal\FindTrasterosByLocalQuery;
use App\Trastero\Application\Query\FindTrasterosDisponibles\FindTrasterosDisponiblesQuery;
use App\Trastero\Application\Query\ListTrasteros\ListTrasterosQuery;
use App\Trastero\Domain\Exception\TrasteroNotFoundException;
use App\Trastero\Domain\Model\TrasteroId;
use App\Trastero\Domain\Repository\TrasteroRepositoryInterface;
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
        private readonly MessageBusInterface $queryBus,
        private readonly ContratoRepositoryInterface $contratoRepository,
        private readonly TrasteroRepositoryInterface $trasteroRepository
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
    }

    #[Route('/{id}', name: 'trasteros_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[OA\Get(summary: 'Obtener trastero', description: 'Obtiene los datos de un trastero por su ID')]
    #[OA\Parameter(name: 'id', in: 'path', description: 'ID del trastero', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Trastero encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Trastero'))]
    #[OA\Response(response: 404, description: 'Trastero no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Error'))]
    public function show(int $id): JsonResponse
    {
        $envelope = $this->queryBus->dispatch(new FindTrasteroQuery($id));
        $handledStamp = $envelope->last(HandledStamp::class);

        /** @var TrasteroResponse $trastero */
        $trastero = $handledStamp->getResult();

        return $this->json($trastero->toArray());
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
    }

    #[Route('/{id}', name: 'trasteros_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    #[OA\Delete(summary: 'Eliminar trastero', description: 'Elimina un trastero')]
    #[OA\Parameter(name: 'id', in: 'path', description: 'ID del trastero', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 204, description: 'Trastero eliminado')]
    #[OA\Response(response: 404, description: 'Trastero no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Error'))]
    public function delete(int $id): JsonResponse
    {
        $this->commandBus->dispatch(new DeleteTrasteroCommand($id));

        return $this->json(null, Response::HTTP_NO_CONTENT);
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
    #[OA\Get(summary: 'Trasteros disponibles', description: 'Obtiene trasteros disponibles. Con parametro fecha, devuelve los que estaran disponibles en esa fecha.')]
    #[OA\Parameter(name: 'fecha', in: 'query', description: 'Fecha para verificar disponibilidad (Y-m-d, default: hoy)', schema: new OA\Schema(type: 'string', format: 'date'))]
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
    public function disponibles(Request $request): JsonResponse
    {
        $fechaStr = $request->query->get('fecha');

        if ($fechaStr !== null) {
            $fecha = new \DateTimeImmutable($fechaStr);
            $trasteros = $this->trasteroRepository->findActiveTrasteros();
            $disponibles = [];

            foreach ($trasteros as $trastero) {
                if ($trastero->estado()->value === 'mantenimiento') {
                    continue;
                }

                $solapados = $this->contratoRepository->findContratosSolapados(
                    $trastero->id()->value,
                    $fecha,
                    $fecha
                );

                if (count($solapados) === 0) {
                    $contratos = $this->contratoRepository->findByTrasteroId($trastero->id()->value);
                    $disponibles[] = TrasteroResponse::fromTrasteroWithContratos($trastero, $contratos);
                }
            }

            return $this->json([
                'data' => array_map(fn(TrasteroResponse $t) => $t->toArray(), $disponibles),
                'meta' => [
                    'total' => count($disponibles),
                    'fecha' => $fecha->format('Y-m-d'),
                ],
            ]);
        }

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

    #[Route('/{id}/disponibilidad', name: 'trasteros_disponibilidad', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[OA\Get(summary: 'Verificar disponibilidad de trastero', description: 'Verifica si un trastero esta disponible en un rango de fechas')]
    #[OA\Parameter(name: 'id', in: 'path', description: 'ID del trastero', schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'desde', in: 'query', required: true, description: 'Fecha inicio (Y-m-d)', schema: new OA\Schema(type: 'string', format: 'date'))]
    #[OA\Parameter(name: 'hasta', in: 'query', description: 'Fecha fin (Y-m-d, opcional)', schema: new OA\Schema(type: 'string', format: 'date'))]
    #[OA\Response(
        response: 200,
        description: 'Estado de disponibilidad',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'disponible', type: 'boolean'),
                new OA\Property(property: 'conflictos', type: 'array', items: new OA\Items(
                    properties: [
                        new OA\Property(property: 'contratoId', type: 'integer'),
                        new OA\Property(property: 'fechaInicio', type: 'string', format: 'date'),
                        new OA\Property(property: 'fechaFin', type: 'string', format: 'date', nullable: true),
                    ],
                    type: 'object'
                ))
            ]
        )
    )]
    public function disponibilidad(int $id, Request $request): JsonResponse
    {
        $trastero = $this->trasteroRepository->findById(TrasteroId::fromInt($id));
        if ($trastero === null) {
            throw TrasteroNotFoundException::withId($id);
        }

        $desdeStr = $request->query->get('desde');
        if ($desdeStr === null) {
            return $this->json([
                'error' => ['message' => 'El parametro desde es obligatorio', 'code' => 'VALIDATION_ERROR'],
            ], Response::HTTP_BAD_REQUEST);
        }

        $desde = new \DateTimeImmutable($desdeStr);
        $hastaStr = $request->query->get('hasta');
        $hasta = $hastaStr !== null ? new \DateTimeImmutable($hastaStr) : null;

        $solapados = $this->contratoRepository->findContratosSolapados(
            $id,
            $desde,
            $hasta
        );

        $conflictos = array_map(fn($contrato) => [
            'contratoId' => $contrato->id()->value,
            'fechaInicio' => $contrato->fechaInicio()->format('Y-m-d'),
            'fechaFin' => $contrato->fechaFin()?->format('Y-m-d'),
        ], $solapados);

        return $this->json([
            'disponible' => count($solapados) === 0,
            'conflictos' => $conflictos,
        ]);
    }
}
