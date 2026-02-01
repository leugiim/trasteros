<?php

declare(strict_types=1);

namespace App\Gasto\Infrastructure\Controller;

use App\Gasto\Application\Command\CreateGasto\CreateGastoCommand;
use App\Gasto\Application\Command\DeleteGasto\DeleteGastoCommand;
use App\Gasto\Application\Command\UpdateGasto\UpdateGastoCommand;
use App\Gasto\Application\DTO\GastoRequest;
use App\Gasto\Application\DTO\GastoResponse;
use App\Gasto\Application\Query\FindGasto\FindGastoQuery;
use App\Gasto\Application\Query\ListGastos\ListGastosQuery;
use App\Gasto\Domain\Exception\GastoNotFoundException;
use App\Gasto\Domain\Exception\InvalidGastoCategoriaException;
use App\Gasto\Domain\Exception\InvalidImporteException;
use App\Gasto\Domain\Exception\InvalidMetodoPagoException;
use App\Local\Domain\Exception\LocalNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/gastos')]
final class GastoController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly MessageBusInterface $queryBus
    ) {
    }

    #[Route('', name: 'gastos_list', methods: ['GET'])]
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

        try {
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
        } catch (InvalidGastoCategoriaException $e) {
            return $this->json([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => 'VALIDATION_ERROR',
                    'details' => [
                        'categoria' => [$e->getMessage()],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => 'VALIDATION_ERROR',
                    'details' => [
                        'fecha' => [$e->getMessage()],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'gastos_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        try {
            $envelope = $this->queryBus->dispatch(new FindGastoQuery($id));
            $handledStamp = $envelope->last(HandledStamp::class);

            /** @var GastoResponse $gasto */
            $gasto = $handledStamp->getResult();

            return $this->json($gasto->toArray());
        } catch (GastoNotFoundException $e) {
            return $this->json([
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => 'GASTO_NOT_FOUND',
                ],
            ], Response::HTTP_NOT_FOUND);
        }
    }

    #[Route('', name: 'gastos_create', methods: ['POST'])]
    public function create(#[MapRequestPayload] GastoRequest $request): JsonResponse
    {
        try {
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
        } catch (InvalidGastoCategoriaException $e) {
            return $this->json([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => 'VALIDATION_ERROR',
                    'details' => [
                        'categoria' => [$e->getMessage()],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        } catch (InvalidMetodoPagoException $e) {
            return $this->json([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => 'VALIDATION_ERROR',
                    'details' => [
                        'metodoPago' => [$e->getMessage()],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        } catch (InvalidImporteException $e) {
            return $this->json([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => 'VALIDATION_ERROR',
                    'details' => [
                        'importe' => [$e->getMessage()],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => 'VALIDATION_ERROR',
                    'details' => [
                        'fecha' => [$e->getMessage()],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'gastos_update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function update(int $id, #[MapRequestPayload] GastoRequest $request): JsonResponse
    {
        try {
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
        } catch (GastoNotFoundException $e) {
            return $this->json([
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => 'GASTO_NOT_FOUND',
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
        } catch (InvalidGastoCategoriaException $e) {
            return $this->json([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => 'VALIDATION_ERROR',
                    'details' => [
                        'categoria' => [$e->getMessage()],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        } catch (InvalidMetodoPagoException $e) {
            return $this->json([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => 'VALIDATION_ERROR',
                    'details' => [
                        'metodoPago' => [$e->getMessage()],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        } catch (InvalidImporteException $e) {
            return $this->json([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => 'VALIDATION_ERROR',
                    'details' => [
                        'importe' => [$e->getMessage()],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => 'VALIDATION_ERROR',
                    'details' => [
                        'fecha' => [$e->getMessage()],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'gastos_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id): JsonResponse
    {
        try {
            $this->commandBus->dispatch(new DeleteGastoCommand($id));

            return $this->json(null, Response::HTTP_NO_CONTENT);
        } catch (GastoNotFoundException $e) {
            return $this->json([
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => 'GASTO_NOT_FOUND',
                ],
            ], Response::HTTP_NOT_FOUND);
        }
    }
}
