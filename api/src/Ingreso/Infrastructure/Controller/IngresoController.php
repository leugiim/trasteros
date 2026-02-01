<?php

declare(strict_types=1);

namespace App\Ingreso\Infrastructure\Controller;

use App\Contrato\Domain\Exception\ContratoNotFoundException;
use App\Ingreso\Application\Command\CreateIngreso\CreateIngresoCommand;
use App\Ingreso\Application\Command\DeleteIngreso\DeleteIngresoCommand;
use App\Ingreso\Application\Command\UpdateIngreso\UpdateIngresoCommand;
use App\Ingreso\Application\DTO\IngresoRequest;
use App\Ingreso\Application\DTO\IngresoResponse;
use App\Ingreso\Application\Query\FindIngreso\FindIngresoQuery;
use App\Ingreso\Application\Query\ListIngresos\ListIngresosQuery;
use App\Ingreso\Domain\Exception\IngresoNotFoundException;
use App\Ingreso\Domain\Exception\InvalidImporteException;
use App\Ingreso\Domain\Exception\InvalidIngresoCategoriaException;
use App\Ingreso\Domain\Exception\InvalidMetodoPagoException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/ingresos')]
final class IngresoController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly MessageBusInterface $queryBus
    ) {
    }

    #[Route('', name: 'ingresos_list', methods: ['GET'])]
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

        try {
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
        } catch (InvalidIngresoCategoriaException $e) {
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

    #[Route('/{id}', name: 'ingresos_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        try {
            $envelope = $this->queryBus->dispatch(new FindIngresoQuery($id));
            $handledStamp = $envelope->last(HandledStamp::class);

            /** @var IngresoResponse $ingreso */
            $ingreso = $handledStamp->getResult();

            return $this->json($ingreso->toArray());
        } catch (IngresoNotFoundException $e) {
            return $this->json([
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => 'INGRESO_NOT_FOUND',
                ],
            ], Response::HTTP_NOT_FOUND);
        }
    }

    #[Route('', name: 'ingresos_create', methods: ['POST'])]
    public function create(#[MapRequestPayload] IngresoRequest $request): JsonResponse
    {
        try {
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
        } catch (ContratoNotFoundException $e) {
            return $this->json([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => 'VALIDATION_ERROR',
                    'details' => [
                        'contratoId' => [$e->getMessage()],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        } catch (InvalidIngresoCategoriaException $e) {
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
                        'fechaPago' => [$e->getMessage()],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'ingresos_update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function update(int $id, #[MapRequestPayload] IngresoRequest $request): JsonResponse
    {
        try {
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
        } catch (IngresoNotFoundException $e) {
            return $this->json([
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => 'INGRESO_NOT_FOUND',
                ],
            ], Response::HTTP_NOT_FOUND);
        } catch (ContratoNotFoundException $e) {
            return $this->json([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => 'VALIDATION_ERROR',
                    'details' => [
                        'contratoId' => [$e->getMessage()],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        } catch (InvalidIngresoCategoriaException $e) {
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
                        'fechaPago' => [$e->getMessage()],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'ingresos_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id): JsonResponse
    {
        try {
            $this->commandBus->dispatch(new DeleteIngresoCommand($id));

            return $this->json(null, Response::HTTP_NO_CONTENT);
        } catch (IngresoNotFoundException $e) {
            return $this->json([
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => 'INGRESO_NOT_FOUND',
                ],
            ], Response::HTTP_NOT_FOUND);
        }
    }
}
