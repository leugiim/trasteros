<?php

declare(strict_types=1);

namespace App\Trastero\Infrastructure\Controller;

use App\Local\Domain\Exception\LocalNotFoundException;
use App\Trastero\Application\Command\CreateTrastero\CreateTrasteroCommand;
use App\Trastero\Application\Command\DeleteTrastero\DeleteTrasteroCommand;
use App\Trastero\Application\Command\UpdateTrastero\UpdateTrasteroCommand;
use App\Trastero\Application\DTO\TrasteroRequest;
use App\Trastero\Application\DTO\TrasteroResponse;
use App\Trastero\Application\Query\FindTrastero\FindTrasteroQuery;
use App\Trastero\Application\Query\ListTrasteros\ListTrasterosQuery;
use App\Trastero\Domain\Exception\DuplicateTrasteroException;
use App\Trastero\Domain\Exception\InvalidPrecioMensualException;
use App\Trastero\Domain\Exception\InvalidSuperficieException;
use App\Trastero\Domain\Exception\InvalidTrasteroEstadoException;
use App\Trastero\Domain\Exception\TrasteroNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;
use App\Auth\Infrastructure\Attribute\Auth;

#[Route('/api/trasteros')]
#[Auth]
final class TrasteroController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly MessageBusInterface $queryBus
    ) {
    }

    #[Route('', name: 'trasteros_list', methods: ['GET'])]
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
}
