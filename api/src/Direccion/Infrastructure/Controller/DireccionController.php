<?php

declare(strict_types=1);

namespace App\Direccion\Infrastructure\Controller;

use App\Direccion\Application\Command\CreateDireccion\CreateDireccionCommand;
use App\Direccion\Application\Command\DeleteDireccion\DeleteDireccionCommand;
use App\Direccion\Application\Command\UpdateDireccion\UpdateDireccionCommand;
use App\Direccion\Application\DTO\DireccionRequest;
use App\Direccion\Application\DTO\DireccionResponse;
use App\Direccion\Application\Query\FindDireccion\FindDireccionQuery;
use App\Direccion\Application\Query\ListDirecciones\ListDireccionesQuery;
use App\Direccion\Domain\Exception\DireccionNotFoundException;
use App\Direccion\Domain\Exception\InvalidCodigoPostalException;
use App\Direccion\Domain\Exception\InvalidCoordenadasException;
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

#[Route('/api/direcciones')]
#[Auth]
#[OA\Tag(name: 'Direcciones')]
final class DireccionController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly MessageBusInterface $queryBus
    ) {
    }

    #[Route('', name: 'direcciones_list', methods: ['GET'])]
    #[OA\Get(summary: 'Listar direcciones', description: 'Obtiene la lista de todas las direcciones')]
    #[OA\Parameter(name: 'ciudad', in: 'query', description: 'Filtrar por ciudad', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'provincia', in: 'query', description: 'Filtrar por provincia', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'codigoPostal', in: 'query', description: 'Filtrar por codigo postal', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'onlyActive', in: 'query', description: 'Solo activas', schema: new OA\Schema(type: 'boolean'))]
    #[OA\Response(
        response: 200,
        description: 'Lista de direcciones',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Direccion')),
                new OA\Property(property: 'meta', ref: '#/components/schemas/PaginatedMeta')
            ]
        )
    )]
    public function list(Request $request): JsonResponse
    {
        $ciudad = $request->query->get('ciudad');
        $provincia = $request->query->get('provincia');
        $codigoPostal = $request->query->get('codigoPostal');
        $onlyActive = $request->query->get('onlyActive');

        $onlyActiveFilter = $onlyActive !== null
            ? filter_var($onlyActive, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
            : null;

        $envelope = $this->queryBus->dispatch(new ListDireccionesQuery(
            ciudad: $ciudad,
            provincia: $provincia,
            codigoPostal: $codigoPostal,
            onlyActive: $onlyActiveFilter
        ));

        $handledStamp = $envelope->last(HandledStamp::class);

        /** @var DireccionResponse[] $direcciones */
        $direcciones = $handledStamp->getResult();

        return $this->json([
            'data' => array_map(fn(DireccionResponse $direccion) => $direccion->toArray(), $direcciones),
            'meta' => [
                'total' => count($direcciones),
            ],
        ]);
    }

    #[Route('/{id}', name: 'direcciones_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[OA\Get(summary: 'Obtener direccion', description: 'Obtiene los datos de una direccion por su ID')]
    #[OA\Parameter(name: 'id', in: 'path', description: 'ID de la direccion', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Direccion encontrada', content: new OA\JsonContent(ref: '#/components/schemas/Direccion'))]
    #[OA\Response(response: 404, description: 'Direccion no encontrada', content: new OA\JsonContent(ref: '#/components/schemas/Error'))]
    public function show(int $id): JsonResponse
    {
        try {
            $envelope = $this->queryBus->dispatch(new FindDireccionQuery($id));
            $handledStamp = $envelope->last(HandledStamp::class);

            /** @var DireccionResponse $direccion */
            $direccion = $handledStamp->getResult();

            return $this->json($direccion->toArray());
        } catch (DireccionNotFoundException $e) {
            return $this->json([
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => 'DIRECCION_NOT_FOUND',
                ],
            ], Response::HTTP_NOT_FOUND);
        }
    }

    #[Route('', name: 'direcciones_create', methods: ['POST'])]
    #[OA\Post(summary: 'Crear direccion', description: 'Crea una nueva direccion')]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['nombreVia', 'codigoPostal', 'ciudad', 'provincia', 'pais'],
            properties: [
                new OA\Property(property: 'tipoVia', type: 'string', nullable: true, example: 'Calle'),
                new OA\Property(property: 'nombreVia', type: 'string', example: 'Gran Via'),
                new OA\Property(property: 'numero', type: 'string', nullable: true, example: '123'),
                new OA\Property(property: 'piso', type: 'string', nullable: true, example: '2'),
                new OA\Property(property: 'puerta', type: 'string', nullable: true, example: 'A'),
                new OA\Property(property: 'codigoPostal', type: 'string', example: '28001'),
                new OA\Property(property: 'ciudad', type: 'string', example: 'Madrid'),
                new OA\Property(property: 'provincia', type: 'string', example: 'Madrid'),
                new OA\Property(property: 'pais', type: 'string', example: 'Espana'),
                new OA\Property(property: 'latitud', type: 'number', format: 'float', nullable: true, example: 40.4168),
                new OA\Property(property: 'longitud', type: 'number', format: 'float', nullable: true, example: -3.7038)
            ]
        )
    )]
    #[OA\Response(response: 201, description: 'Direccion creada', content: new OA\JsonContent(ref: '#/components/schemas/Direccion'))]
    #[OA\Response(response: 400, description: 'Error de validacion', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))]
    public function create(#[MapRequestPayload] DireccionRequest $request): JsonResponse
    {
        try {
            $envelope = $this->commandBus->dispatch(new CreateDireccionCommand(
                nombreVia: $request->nombreVia,
                codigoPostal: $request->codigoPostal,
                ciudad: $request->ciudad,
                provincia: $request->provincia,
                pais: $request->pais,
                tipoVia: $request->tipoVia,
                numero: $request->numero,
                piso: $request->piso,
                puerta: $request->puerta,
                latitud: $request->latitud,
                longitud: $request->longitud
            ));

            $handledStamp = $envelope->last(HandledStamp::class);

            /** @var DireccionResponse $direccion */
            $direccion = $handledStamp->getResult();

            return $this->json($direccion->toArray(), Response::HTTP_CREATED);
        } catch (InvalidCodigoPostalException $e) {
            return $this->json([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => 'VALIDATION_ERROR',
                    'details' => [
                        'codigoPostal' => [$e->getMessage()],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        } catch (InvalidCoordenadasException $e) {
            return $this->json([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => 'VALIDATION_ERROR',
                    'details' => [
                        'coordenadas' => [$e->getMessage()],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'direcciones_update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    #[OA\Put(summary: 'Actualizar direccion', description: 'Actualiza los datos de una direccion')]
    #[OA\Parameter(name: 'id', in: 'path', description: 'ID de la direccion', schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['nombreVia', 'codigoPostal', 'ciudad', 'provincia', 'pais'],
            properties: [
                new OA\Property(property: 'tipoVia', type: 'string', nullable: true),
                new OA\Property(property: 'nombreVia', type: 'string'),
                new OA\Property(property: 'numero', type: 'string', nullable: true),
                new OA\Property(property: 'piso', type: 'string', nullable: true),
                new OA\Property(property: 'puerta', type: 'string', nullable: true),
                new OA\Property(property: 'codigoPostal', type: 'string'),
                new OA\Property(property: 'ciudad', type: 'string'),
                new OA\Property(property: 'provincia', type: 'string'),
                new OA\Property(property: 'pais', type: 'string'),
                new OA\Property(property: 'latitud', type: 'number', format: 'float', nullable: true),
                new OA\Property(property: 'longitud', type: 'number', format: 'float', nullable: true)
            ]
        )
    )]
    #[OA\Response(response: 200, description: 'Direccion actualizada', content: new OA\JsonContent(ref: '#/components/schemas/Direccion'))]
    #[OA\Response(response: 400, description: 'Error de validacion', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))]
    #[OA\Response(response: 404, description: 'Direccion no encontrada', content: new OA\JsonContent(ref: '#/components/schemas/Error'))]
    public function update(int $id, #[MapRequestPayload] DireccionRequest $request): JsonResponse
    {
        try {
            $envelope = $this->commandBus->dispatch(new UpdateDireccionCommand(
                id: $id,
                nombreVia: $request->nombreVia,
                codigoPostal: $request->codigoPostal,
                ciudad: $request->ciudad,
                provincia: $request->provincia,
                pais: $request->pais,
                tipoVia: $request->tipoVia,
                numero: $request->numero,
                piso: $request->piso,
                puerta: $request->puerta,
                latitud: $request->latitud,
                longitud: $request->longitud
            ));

            $handledStamp = $envelope->last(HandledStamp::class);

            /** @var DireccionResponse $direccion */
            $direccion = $handledStamp->getResult();

            return $this->json($direccion->toArray());
        } catch (DireccionNotFoundException $e) {
            return $this->json([
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => 'DIRECCION_NOT_FOUND',
                ],
            ], Response::HTTP_NOT_FOUND);
        } catch (InvalidCodigoPostalException $e) {
            return $this->json([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => 'VALIDATION_ERROR',
                    'details' => [
                        'codigoPostal' => [$e->getMessage()],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        } catch (InvalidCoordenadasException $e) {
            return $this->json([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => 'VALIDATION_ERROR',
                    'details' => [
                        'coordenadas' => [$e->getMessage()],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'direcciones_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    #[OA\Delete(summary: 'Eliminar direccion', description: 'Elimina una direccion')]
    #[OA\Parameter(name: 'id', in: 'path', description: 'ID de la direccion', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 204, description: 'Direccion eliminada')]
    #[OA\Response(response: 404, description: 'Direccion no encontrada', content: new OA\JsonContent(ref: '#/components/schemas/Error'))]
    public function delete(int $id): JsonResponse
    {
        try {
            $this->commandBus->dispatch(new DeleteDireccionCommand($id));

            return $this->json(null, Response::HTTP_NO_CONTENT);
        } catch (DireccionNotFoundException $e) {
            return $this->json([
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => 'DIRECCION_NOT_FOUND',
                ],
            ], Response::HTTP_NOT_FOUND);
        }
    }
}
