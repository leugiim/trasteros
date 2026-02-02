<?php

declare(strict_types=1);

namespace App\Local\Infrastructure\Controller;

use App\Direccion\Domain\Exception\DireccionNotFoundException;
use App\Local\Application\Command\CreateLocal\CreateLocalCommand;
use App\Local\Application\Command\DeleteLocal\DeleteLocalCommand;
use App\Local\Application\Command\UpdateLocal\UpdateLocalCommand;
use App\Local\Application\DTO\LocalRequest;
use App\Local\Application\DTO\LocalResponse;
use App\Local\Application\Query\FindLocal\FindLocalQuery;
use App\Local\Application\Query\ListLocales\ListLocalesQuery;
use App\Local\Domain\Exception\InvalidReferenciaCatastralException;
use App\Local\Domain\Exception\LocalNotFoundException;
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

#[Route('/api/locales')]
#[Auth]
#[OA\Tag(name: 'Locales')]
final class LocalController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly MessageBusInterface $queryBus
    ) {
    }

    #[Route('', name: 'locales_list', methods: ['GET'])]
    #[OA\Get(summary: 'Listar locales', description: 'Obtiene la lista de todos los locales')]
    #[OA\Parameter(name: 'nombre', in: 'query', description: 'Filtrar por nombre', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'direccionId', in: 'query', description: 'Filtrar por direccion', schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'onlyActive', in: 'query', description: 'Solo activos', schema: new OA\Schema(type: 'boolean'))]
    #[OA\Response(
        response: 200,
        description: 'Lista de locales',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Local')),
                new OA\Property(property: 'meta', ref: '#/components/schemas/PaginatedMeta')
            ]
        )
    )]
    public function list(Request $request): JsonResponse
    {
        $nombre = $request->query->get('nombre');
        $direccionId = $request->query->get('direccionId');
        $onlyActive = $request->query->get('onlyActive');

        $direccionIdFilter = $direccionId !== null ? (int) $direccionId : null;
        $onlyActiveFilter = $onlyActive !== null
            ? filter_var($onlyActive, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
            : null;

        $envelope = $this->queryBus->dispatch(new ListLocalesQuery(
            nombre: $nombre,
            direccionId: $direccionIdFilter,
            onlyActive: $onlyActiveFilter
        ));

        $handledStamp = $envelope->last(HandledStamp::class);

        /** @var LocalResponse[] $locales */
        $locales = $handledStamp->getResult();

        return $this->json([
            'data' => array_map(fn(LocalResponse $local) => $local->toArray(), $locales),
            'meta' => [
                'total' => count($locales),
            ],
        ]);
    }

    #[Route('/{id}', name: 'locales_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[OA\Get(summary: 'Obtener local', description: 'Obtiene los datos de un local por su ID')]
    #[OA\Parameter(name: 'id', in: 'path', description: 'ID del local', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Local encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Local'))]
    #[OA\Response(response: 404, description: 'Local no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Error'))]
    public function show(int $id): JsonResponse
    {
        try {
            $envelope = $this->queryBus->dispatch(new FindLocalQuery($id));
            $handledStamp = $envelope->last(HandledStamp::class);

            /** @var LocalResponse $local */
            $local = $handledStamp->getResult();

            return $this->json($local->toArray());
        } catch (LocalNotFoundException $e) {
            return $this->json([
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => 'LOCAL_NOT_FOUND',
                ],
            ], Response::HTTP_NOT_FOUND);
        }
    }

    #[Route('', name: 'locales_create', methods: ['POST'])]
    #[OA\Post(summary: 'Crear local', description: 'Crea un nuevo local')]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['nombre', 'direccionId'],
            properties: [
                new OA\Property(property: 'nombre', type: 'string', example: 'Local Centro'),
                new OA\Property(property: 'direccionId', type: 'integer'),
                new OA\Property(property: 'superficieTotal', type: 'number', format: 'float', nullable: true, example: 500.0),
                new OA\Property(property: 'numeroTrasteros', type: 'integer', nullable: true, example: 20),
                new OA\Property(property: 'fechaCompra', type: 'string', format: 'date', nullable: true, example: '2020-01-15'),
                new OA\Property(property: 'precioCompra', type: 'number', format: 'float', nullable: true, example: 150000.0),
                new OA\Property(property: 'referenciaCatastral', type: 'string', nullable: true, example: '1234567890123456789A'),
                new OA\Property(property: 'valorCatastral', type: 'number', format: 'float', nullable: true, example: 120000.0)
            ]
        )
    )]
    #[OA\Response(response: 201, description: 'Local creado', content: new OA\JsonContent(ref: '#/components/schemas/Local'))]
    #[OA\Response(response: 400, description: 'Error de validacion', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))]
    public function create(#[MapRequestPayload] LocalRequest $request): JsonResponse
    {
        try {
            $envelope = $this->commandBus->dispatch(new CreateLocalCommand(
                nombre: $request->nombre,
                direccionId: $request->direccionId,
                superficieTotal: $request->superficieTotal,
                numeroTrasteros: $request->numeroTrasteros,
                fechaCompra: $request->fechaCompra,
                precioCompra: $request->precioCompra,
                referenciaCatastral: $request->referenciaCatastral,
                valorCatastral: $request->valorCatastral
            ));

            $handledStamp = $envelope->last(HandledStamp::class);

            /** @var LocalResponse $local */
            $local = $handledStamp->getResult();

            return $this->json($local->toArray(), Response::HTTP_CREATED);
        } catch (DireccionNotFoundException $e) {
            return $this->json([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => 'VALIDATION_ERROR',
                    'details' => [
                        'direccionId' => [$e->getMessage()],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        } catch (InvalidReferenciaCatastralException $e) {
            return $this->json([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => 'VALIDATION_ERROR',
                    'details' => [
                        'referenciaCatastral' => [$e->getMessage()],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => 'VALIDATION_ERROR',
                    'details' => [
                        'fechaCompra' => [$e->getMessage()],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'locales_update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    #[OA\Put(summary: 'Actualizar local', description: 'Actualiza los datos de un local')]
    #[OA\Parameter(name: 'id', in: 'path', description: 'ID del local', schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['nombre', 'direccionId'],
            properties: [
                new OA\Property(property: 'nombre', type: 'string'),
                new OA\Property(property: 'direccionId', type: 'integer'),
                new OA\Property(property: 'superficieTotal', type: 'number', format: 'float', nullable: true),
                new OA\Property(property: 'numeroTrasteros', type: 'integer', nullable: true),
                new OA\Property(property: 'fechaCompra', type: 'string', format: 'date', nullable: true),
                new OA\Property(property: 'precioCompra', type: 'number', format: 'float', nullable: true),
                new OA\Property(property: 'referenciaCatastral', type: 'string', nullable: true),
                new OA\Property(property: 'valorCatastral', type: 'number', format: 'float', nullable: true)
            ]
        )
    )]
    #[OA\Response(response: 200, description: 'Local actualizado', content: new OA\JsonContent(ref: '#/components/schemas/Local'))]
    #[OA\Response(response: 400, description: 'Error de validacion', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))]
    #[OA\Response(response: 404, description: 'Local no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Error'))]
    public function update(int $id, #[MapRequestPayload] LocalRequest $request): JsonResponse
    {
        try {
            $envelope = $this->commandBus->dispatch(new UpdateLocalCommand(
                id: $id,
                nombre: $request->nombre,
                direccionId: $request->direccionId,
                superficieTotal: $request->superficieTotal,
                numeroTrasteros: $request->numeroTrasteros,
                fechaCompra: $request->fechaCompra,
                precioCompra: $request->precioCompra,
                referenciaCatastral: $request->referenciaCatastral,
                valorCatastral: $request->valorCatastral
            ));

            $handledStamp = $envelope->last(HandledStamp::class);

            /** @var LocalResponse $local */
            $local = $handledStamp->getResult();

            return $this->json($local->toArray());
        } catch (LocalNotFoundException $e) {
            return $this->json([
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => 'LOCAL_NOT_FOUND',
                ],
            ], Response::HTTP_NOT_FOUND);
        } catch (DireccionNotFoundException $e) {
            return $this->json([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => 'VALIDATION_ERROR',
                    'details' => [
                        'direccionId' => [$e->getMessage()],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        } catch (InvalidReferenciaCatastralException $e) {
            return $this->json([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => 'VALIDATION_ERROR',
                    'details' => [
                        'referenciaCatastral' => [$e->getMessage()],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => 'VALIDATION_ERROR',
                    'details' => [
                        'fechaCompra' => [$e->getMessage()],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'locales_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    #[OA\Delete(summary: 'Eliminar local', description: 'Elimina un local')]
    #[OA\Parameter(name: 'id', in: 'path', description: 'ID del local', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 204, description: 'Local eliminado')]
    #[OA\Response(response: 404, description: 'Local no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Error'))]
    public function delete(int $id): JsonResponse
    {
        try {
            $this->commandBus->dispatch(new DeleteLocalCommand($id));

            return $this->json(null, Response::HTTP_NO_CONTENT);
        } catch (LocalNotFoundException $e) {
            return $this->json([
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => 'LOCAL_NOT_FOUND',
                ],
            ], Response::HTTP_NOT_FOUND);
        }
    }
}
