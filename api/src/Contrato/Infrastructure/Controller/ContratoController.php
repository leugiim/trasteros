<?php

declare(strict_types=1);

namespace App\Contrato\Infrastructure\Controller;

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
use App\Contrato\Application\Query\FindContratosFianzasPendientes\FindContratosFianzasPendientesQuery;
use App\Contrato\Application\Query\FindContratosProximosAVencer\FindContratosProximosAVencerQuery;
use App\Contrato\Application\Query\ListContratos\ListContratosQuery;
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

#[Route('/api/contratos')]
#[Auth]
#[OA\Tag(name: 'Contratos')]
final class ContratoController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly MessageBusInterface $queryBus
    ) {
    }

    #[Route('', name: 'contratos_list', methods: ['GET'])]
    #[OA\Get(summary: 'Listar contratos', description: 'Obtiene la lista de todos los contratos')]
    #[OA\Parameter(name: 'estado', in: 'query', description: 'Filtrar por estado', schema: new OA\Schema(type: 'string', enum: ['activo', 'finalizado', 'cancelado']))]
    #[OA\Response(
        response: 200,
        description: 'Lista de contratos',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Contrato')),
                new OA\Property(property: 'meta', ref: '#/components/schemas/PaginatedMeta')
            ]
        )
    )]
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
    #[OA\Get(summary: 'Obtener contrato', description: 'Obtiene los datos de un contrato por su ID')]
    #[OA\Parameter(name: 'id', in: 'path', description: 'ID del contrato', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Contrato encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Contrato'))]
    #[OA\Response(response: 404, description: 'Contrato no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Error'))]
    public function show(int $id): JsonResponse
    {
        $envelope = $this->queryBus->dispatch(new FindContratoQuery($id));
        $handledStamp = $envelope->last(HandledStamp::class);

        /** @var ContratoResponse $contrato */
        $contrato = $handledStamp->getResult();

        return $this->json($contrato->toArray());
    }

    #[Route('/trastero/{trasteroId}', name: 'contratos_by_trastero', methods: ['GET'], requirements: ['trasteroId' => '\d+'])]
    #[OA\Get(summary: 'Contratos por trastero', description: 'Obtiene todos los contratos de un trastero especifico')]
    #[OA\Parameter(name: 'trasteroId', in: 'path', description: 'ID del trastero', schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'onlyActivos', in: 'query', description: 'Solo contratos activos', schema: new OA\Schema(type: 'boolean'))]
    #[OA\Response(
        response: 200,
        description: 'Lista de contratos del trastero',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Contrato')),
                new OA\Property(property: 'meta', ref: '#/components/schemas/PaginatedMeta')
            ]
        )
    )]
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
    #[OA\Get(summary: 'Contratos por cliente', description: 'Obtiene todos los contratos de un cliente especifico')]
    #[OA\Parameter(name: 'clienteId', in: 'path', description: 'ID del cliente', schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'onlyActivos', in: 'query', description: 'Solo contratos activos', schema: new OA\Schema(type: 'boolean'))]
    #[OA\Response(
        response: 200,
        description: 'Lista de contratos del cliente',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Contrato')),
                new OA\Property(property: 'meta', ref: '#/components/schemas/PaginatedMeta')
            ]
        )
    )]
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
    #[OA\Post(summary: 'Crear contrato', description: 'Crea un nuevo contrato')]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['trasteroId', 'clienteId', 'fechaInicio', 'precioMensual'],
            properties: [
                new OA\Property(property: 'trasteroId', type: 'integer'),
                new OA\Property(property: 'clienteId', type: 'integer'),
                new OA\Property(property: 'fechaInicio', type: 'string', format: 'date', example: '2024-01-01'),
                new OA\Property(property: 'fechaFin', type: 'string', format: 'date', nullable: true, example: '2024-12-31'),
                new OA\Property(property: 'precioMensual', type: 'number', format: 'float', example: 50.0),
                new OA\Property(property: 'fianza', type: 'number', format: 'float', nullable: true, example: 100.0),
                new OA\Property(property: 'fianzaPagada', type: 'boolean', default: false),
                new OA\Property(property: 'estado', type: 'string', enum: ['activo', 'finalizado', 'cancelado'], default: 'activo')
            ]
        )
    )]
    #[OA\Response(response: 201, description: 'Contrato creado', content: new OA\JsonContent(ref: '#/components/schemas/Contrato'))]
    #[OA\Response(response: 400, description: 'Error de validacion', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))]
    #[OA\Response(response: 404, description: 'Recurso no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Error'))]
    #[OA\Response(response: 409, description: 'Trastero ya alquilado', content: new OA\JsonContent(ref: '#/components/schemas/Error'))]
    public function create(#[MapRequestPayload] ContratoRequest $request): JsonResponse
    {
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
    }

    #[Route('/{id}', name: 'contratos_update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    #[OA\Put(summary: 'Actualizar contrato', description: 'Actualiza los datos de un contrato')]
    #[OA\Parameter(name: 'id', in: 'path', description: 'ID del contrato', schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['trasteroId', 'clienteId', 'fechaInicio', 'precioMensual'],
            properties: [
                new OA\Property(property: 'trasteroId', type: 'integer'),
                new OA\Property(property: 'clienteId', type: 'integer'),
                new OA\Property(property: 'fechaInicio', type: 'string', format: 'date'),
                new OA\Property(property: 'fechaFin', type: 'string', format: 'date', nullable: true),
                new OA\Property(property: 'precioMensual', type: 'number', format: 'float'),
                new OA\Property(property: 'fianza', type: 'number', format: 'float', nullable: true),
                new OA\Property(property: 'fianzaPagada', type: 'boolean'),
                new OA\Property(property: 'estado', type: 'string', enum: ['activo', 'finalizado', 'cancelado'])
            ]
        )
    )]
    #[OA\Response(response: 200, description: 'Contrato actualizado', content: new OA\JsonContent(ref: '#/components/schemas/Contrato'))]
    #[OA\Response(response: 400, description: 'Error de validacion', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))]
    #[OA\Response(response: 404, description: 'Recurso no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Error'))]
    #[OA\Response(response: 409, description: 'Trastero ya alquilado', content: new OA\JsonContent(ref: '#/components/schemas/Error'))]
    public function update(int $id, #[MapRequestPayload] ContratoRequest $request): JsonResponse
    {
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
    }

    #[Route('/{id}', name: 'contratos_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    #[OA\Delete(summary: 'Eliminar contrato', description: 'Elimina un contrato')]
    #[OA\Parameter(name: 'id', in: 'path', description: 'ID del contrato', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 204, description: 'Contrato eliminado')]
    #[OA\Response(response: 404, description: 'Contrato no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Error'))]
    public function delete(int $id): JsonResponse
    {
        $this->commandBus->dispatch(new DeleteContratoCommand($id));

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/{id}/finalizar', name: 'contratos_finalizar', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    #[OA\Patch(summary: 'Finalizar contrato', description: 'Marca un contrato como finalizado')]
    #[OA\Parameter(name: 'id', in: 'path', description: 'ID del contrato', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Contrato finalizado', content: new OA\JsonContent(ref: '#/components/schemas/Contrato'))]
    #[OA\Response(response: 404, description: 'Contrato no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Error'))]
    public function finalizar(int $id): JsonResponse
    {
        $envelope = $this->commandBus->dispatch(new FinalizarContratoCommand($id));
        $handledStamp = $envelope->last(HandledStamp::class);

        /** @var ContratoResponse $contrato */
        $contrato = $handledStamp->getResult();

        return $this->json($contrato->toArray());
    }

    #[Route('/{id}/cancelar', name: 'contratos_cancelar', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    #[OA\Patch(summary: 'Cancelar contrato', description: 'Marca un contrato como cancelado')]
    #[OA\Parameter(name: 'id', in: 'path', description: 'ID del contrato', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Contrato cancelado', content: new OA\JsonContent(ref: '#/components/schemas/Contrato'))]
    #[OA\Response(response: 404, description: 'Contrato no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Error'))]
    public function cancelar(int $id): JsonResponse
    {
        $envelope = $this->commandBus->dispatch(new CancelarContratoCommand($id));
        $handledStamp = $envelope->last(HandledStamp::class);

        /** @var ContratoResponse $contrato */
        $contrato = $handledStamp->getResult();

        return $this->json($contrato->toArray());
    }

    #[Route('/{id}/marcar-fianza-pagada', name: 'contratos_marcar_fianza_pagada', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    #[OA\Patch(summary: 'Marcar fianza como pagada', description: 'Marca la fianza de un contrato como pagada')]
    #[OA\Parameter(name: 'id', in: 'path', description: 'ID del contrato', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Fianza marcada como pagada', content: new OA\JsonContent(ref: '#/components/schemas/Contrato'))]
    #[OA\Response(response: 404, description: 'Contrato no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Error'))]
    public function marcarFianzaPagada(int $id): JsonResponse
    {
        $envelope = $this->commandBus->dispatch(new MarcarFianzaPagadaCommand($id));
        $handledStamp = $envelope->last(HandledStamp::class);

        /** @var ContratoResponse $contrato */
        $contrato = $handledStamp->getResult();

        return $this->json($contrato->toArray());
    }

    #[Route('/proximos-vencer', name: 'contratos_proximos_vencer', methods: ['GET'])]
    #[OA\Get(summary: 'Contratos proximos a vencer', description: 'Obtiene los contratos que vencen en los proximos dias')]
    #[OA\Parameter(name: 'dias', in: 'query', description: 'Numero de dias para el vencimiento (por defecto 30)', schema: new OA\Schema(type: 'integer', default: 30))]
    #[OA\Response(
        response: 200,
        description: 'Lista de contratos proximos a vencer',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Contrato')),
                new OA\Property(property: 'meta', properties: [
                    new OA\Property(property: 'total', type: 'integer'),
                    new OA\Property(property: 'dias', type: 'integer')
                ], type: 'object')
            ]
        )
    )]
    public function proximosAVencer(Request $request): JsonResponse
    {
        $dias = $request->query->getInt('dias', 30);

        $envelope = $this->queryBus->dispatch(new FindContratosProximosAVencerQuery($dias));
        $handledStamp = $envelope->last(HandledStamp::class);

        /** @var ContratoResponse[] $contratos */
        $contratos = $handledStamp->getResult();

        return $this->json([
            'data' => array_map(fn(ContratoResponse $contrato) => $contrato->toArray(), $contratos),
            'meta' => [
                'total' => count($contratos),
                'dias' => $dias,
            ],
        ]);
    }

    #[Route('/fianzas-pendientes', name: 'contratos_fianzas_pendientes', methods: ['GET'])]
    #[OA\Get(summary: 'Contratos con fianzas pendientes', description: 'Obtiene los contratos que tienen fianzas pendientes de pago')]
    #[OA\Response(
        response: 200,
        description: 'Lista de contratos con fianzas pendientes',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Contrato')),
                new OA\Property(property: 'meta', ref: '#/components/schemas/PaginatedMeta')
            ]
        )
    )]
    public function fianzasPendientes(): JsonResponse
    {
        $envelope = $this->queryBus->dispatch(new FindContratosFianzasPendientesQuery());
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
}
