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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/locales')]
final class LocalController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly MessageBusInterface $queryBus
    ) {
    }

    #[Route('', name: 'locales_list', methods: ['GET'])]
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
