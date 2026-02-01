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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/direcciones')]
final class DireccionController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly MessageBusInterface $queryBus
    ) {
    }

    #[Route('', name: 'direcciones_list', methods: ['GET'])]
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
