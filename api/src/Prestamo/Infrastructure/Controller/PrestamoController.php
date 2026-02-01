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
use App\Prestamo\Application\Query\ListPrestamos\ListPrestamosQuery;
use App\Prestamo\Domain\Exception\InvalidCapitalSolicitadoException;
use App\Prestamo\Domain\Exception\InvalidPrestamoEstadoException;
use App\Prestamo\Domain\Exception\InvalidTipoInteresException;
use App\Prestamo\Domain\Exception\InvalidTotalADevolverException;
use App\Prestamo\Domain\Exception\PrestamoNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/prestamos')]
final class PrestamoController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly MessageBusInterface $queryBus
    ) {
    }

    #[Route('', name: 'prestamos_list', methods: ['GET'])]
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
}
