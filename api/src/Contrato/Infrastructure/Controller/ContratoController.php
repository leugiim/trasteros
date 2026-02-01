<?php

declare(strict_types=1);

namespace App\Contrato\Infrastructure\Controller;

use App\Cliente\Domain\Exception\ClienteNotFoundException;
use App\Contrato\Application\Command\CancelarContrato\CancelarContratoCommand;
use App\Contrato\Application\Command\CreateContrato\CreateContratoCommand;
use App\Contrato\Application\Command\DeleteContrato\DeleteContratoCommand;
use App\Contrato\Application\Command\FinalizarContrato\FinalizarContratoCommand;
use App\Contrato\Application\Command\MarcarFianzaPagada\MarcarFianzaPagadaCommand;
use App\Contrato\Application\Command\UpdateContrato\UpdateContratoCommand;
use App\Contrato\Application\DTO\ContratoRequest;
use App\Contrato\Application\DTO\ContratoResponse;
use App\Contrato\Application\Query\FindContrato\FindContratoQuery;
use App\Contrato\Application\Query\FindContratosByCliente\FindContratosByClienteQuery;
use App\Contrato\Application\Query\FindContratosByTrastero\FindContratosByTrasteroQuery;
use App\Contrato\Application\Query\ListContratos\ListContratosQuery;
use App\Contrato\Domain\Exception\ContratoNotFoundException;
use App\Contrato\Domain\Exception\InvalidContratoDateException;
use App\Contrato\Domain\Exception\InvalidFianzaException;
use App\Contrato\Domain\Exception\InvalidPrecioMensualException;
use App\Contrato\Domain\Exception\TrasteroAlreadyRentedException;
use App\Trastero\Domain\Exception\TrasteroNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/contratos')]
final class ContratoController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly MessageBusInterface $queryBus
    ) {
    }

    #[Route('', name: 'contratos_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $estado = $request->query->get('estado');

        $envelope = $this->queryBus->dispatch(new ListContratosQuery(
            estado: $estado
        ));

        $handledStamp = $envelope->last(HandledStamp::class);

        /** @var ContratoResponse[] $contratos */
        $contratos = $handledStamp->getResult();

        return $this->json([
            'data' => array_map(fn(ContratoResponse $contrato) => $contrato->toArray(), $contratos),
            'meta' => [
                'total' => count($contratos),
            ],
        ]);
    }

    #[Route('/{id}', name: 'contratos_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        try {
            $envelope = $this->queryBus->dispatch(new FindContratoQuery($id));
            $handledStamp = $envelope->last(HandledStamp::class);

            /** @var ContratoResponse $contrato */
            $contrato = $handledStamp->getResult();

            return $this->json($contrato->toArray());
        } catch (ContratoNotFoundException $e) {
            return $this->json([
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => 'CONTRATO_NOT_FOUND',
                ],
            ], Response::HTTP_NOT_FOUND);
        }
    }

    #[Route('/trastero/{trasteroId}', name: 'contratos_by_trastero', methods: ['GET'], requirements: ['trasteroId' => '\d+'])]
    public function byTrastero(int $trasteroId, Request $request): JsonResponse
    {
        $onlyActivos = $request->query->get('onlyActivos');
        $onlyActivosFilter = $onlyActivos !== null
            ? filter_var($onlyActivos, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false
            : false;

        $envelope = $this->queryBus->dispatch(new FindContratosByTrasteroQuery(
            trasteroId: $trasteroId,
            onlyActivos: $onlyActivosFilter
        ));

        $handledStamp = $envelope->last(HandledStamp::class);

        /** @var ContratoResponse[] $contratos */
        $contratos = $handledStamp->getResult();

        return $this->json([
            'data' => array_map(fn(ContratoResponse $contrato) => $contrato->toArray(), $contratos),
            'meta' => [
                'total' => count($contratos),
            ],
        ]);
    }

    #[Route('/cliente/{clienteId}', name: 'contratos_by_cliente', methods: ['GET'], requirements: ['clienteId' => '\d+'])]
    public function byCliente(int $clienteId, Request $request): JsonResponse
    {
        $onlyActivos = $request->query->get('onlyActivos');
        $onlyActivosFilter = $onlyActivos !== null
            ? filter_var($onlyActivos, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false
            : false;

        $envelope = $this->queryBus->dispatch(new FindContratosByClienteQuery(
            clienteId: $clienteId,
            onlyActivos: $onlyActivosFilter
        ));

        $handledStamp = $envelope->last(HandledStamp::class);

        /** @var ContratoResponse[] $contratos */
        $contratos = $handledStamp->getResult();

        return $this->json([
            'data' => array_map(fn(ContratoResponse $contrato) => $contrato->toArray(), $contratos),
            'meta' => [
                'total' => count($contratos),
            ],
        ]);
    }

    #[Route('', name: 'contratos_create', methods: ['POST'])]
    public function create(#[MapRequestPayload] ContratoRequest $request): JsonResponse
    {
        try {
            $envelope = $this->commandBus->dispatch(new CreateContratoCommand(
                trasteroId: $request->trasteroId,
                clienteId: $request->clienteId,
                fechaInicio: $request->fechaInicio,
                precioMensual: $request->precioMensual,
                fechaFin: $request->fechaFin,
                fianza: $request->fianza,
                fianzaPagada: $request->fianzaPagada,
                estado: $request->estado
            ));

            $handledStamp = $envelope->last(HandledStamp::class);

            /** @var ContratoResponse $contrato */
            $contrato = $handledStamp->getResult();

            return $this->json($contrato->toArray(), Response::HTTP_CREATED);
        } catch (TrasteroNotFoundException | ClienteNotFoundException $e) {
            return $this->json([
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => 'RESOURCE_NOT_FOUND',
                ],
            ], Response::HTTP_NOT_FOUND);
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
        } catch (InvalidFianzaException $e) {
            return $this->json([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => 'VALIDATION_ERROR',
                    'details' => [
                        'fianza' => [$e->getMessage()],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        } catch (InvalidContratoDateException $e) {
            return $this->json([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => 'VALIDATION_ERROR',
                    'details' => [
                        'fechaFin' => [$e->getMessage()],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        } catch (TrasteroAlreadyRentedException $e) {
            return $this->json([
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => 'TRASTERO_ALREADY_RENTED',
                ],
            ], Response::HTTP_CONFLICT);
        } catch (\Exception $e) {
            return $this->json([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => 'VALIDATION_ERROR',
                    'details' => [
                        'general' => [$e->getMessage()],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'contratos_update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function update(int $id, #[MapRequestPayload] ContratoRequest $request): JsonResponse
    {
        try {
            $envelope = $this->commandBus->dispatch(new UpdateContratoCommand(
                id: $id,
                trasteroId: $request->trasteroId,
                clienteId: $request->clienteId,
                fechaInicio: $request->fechaInicio,
                precioMensual: $request->precioMensual,
                fechaFin: $request->fechaFin,
                fianza: $request->fianza,
                fianzaPagada: $request->fianzaPagada,
                estado: $request->estado
            ));

            $handledStamp = $envelope->last(HandledStamp::class);

            /** @var ContratoResponse $contrato */
            $contrato = $handledStamp->getResult();

            return $this->json($contrato->toArray());
        } catch (ContratoNotFoundException | TrasteroNotFoundException | ClienteNotFoundException $e) {
            return $this->json([
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => 'RESOURCE_NOT_FOUND',
                ],
            ], Response::HTTP_NOT_FOUND);
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
        } catch (InvalidFianzaException $e) {
            return $this->json([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => 'VALIDATION_ERROR',
                    'details' => [
                        'fianza' => [$e->getMessage()],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        } catch (InvalidContratoDateException $e) {
            return $this->json([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => 'VALIDATION_ERROR',
                    'details' => [
                        'fechaFin' => [$e->getMessage()],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        } catch (TrasteroAlreadyRentedException $e) {
            return $this->json([
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => 'TRASTERO_ALREADY_RENTED',
                ],
            ], Response::HTTP_CONFLICT);
        } catch (\Exception $e) {
            return $this->json([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => 'VALIDATION_ERROR',
                    'details' => [
                        'general' => [$e->getMessage()],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'contratos_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id): JsonResponse
    {
        try {
            $this->commandBus->dispatch(new DeleteContratoCommand($id));

            return $this->json(null, Response::HTTP_NO_CONTENT);
        } catch (ContratoNotFoundException $e) {
            return $this->json([
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => 'CONTRATO_NOT_FOUND',
                ],
            ], Response::HTTP_NOT_FOUND);
        }
    }

    #[Route('/{id}/finalizar', name: 'contratos_finalizar', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    public function finalizar(int $id): JsonResponse
    {
        try {
            $envelope = $this->commandBus->dispatch(new FinalizarContratoCommand($id));
            $handledStamp = $envelope->last(HandledStamp::class);

            /** @var ContratoResponse $contrato */
            $contrato = $handledStamp->getResult();

            return $this->json($contrato->toArray());
        } catch (ContratoNotFoundException $e) {
            return $this->json([
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => 'CONTRATO_NOT_FOUND',
                ],
            ], Response::HTTP_NOT_FOUND);
        }
    }

    #[Route('/{id}/cancelar', name: 'contratos_cancelar', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    public function cancelar(int $id): JsonResponse
    {
        try {
            $envelope = $this->commandBus->dispatch(new CancelarContratoCommand($id));
            $handledStamp = $envelope->last(HandledStamp::class);

            /** @var ContratoResponse $contrato */
            $contrato = $handledStamp->getResult();

            return $this->json($contrato->toArray());
        } catch (ContratoNotFoundException $e) {
            return $this->json([
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => 'CONTRATO_NOT_FOUND',
                ],
            ], Response::HTTP_NOT_FOUND);
        }
    }

    #[Route('/{id}/marcar-fianza-pagada', name: 'contratos_marcar_fianza_pagada', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    public function marcarFianzaPagada(int $id): JsonResponse
    {
        try {
            $envelope = $this->commandBus->dispatch(new MarcarFianzaPagadaCommand($id));
            $handledStamp = $envelope->last(HandledStamp::class);

            /** @var ContratoResponse $contrato */
            $contrato = $handledStamp->getResult();

            return $this->json($contrato->toArray());
        } catch (ContratoNotFoundException $e) {
            return $this->json([
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => 'CONTRATO_NOT_FOUND',
                ],
            ], Response::HTTP_NOT_FOUND);
        }
    }
}
