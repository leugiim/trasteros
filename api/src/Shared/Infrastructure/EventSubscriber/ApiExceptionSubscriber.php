<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\Exception\ValidationFailedException;

final class ApiExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            // Lower priority than MessengerExceptionSubscriber (100), acts as fallback
            KernelEvents::EXCEPTION => ['onKernelException', 50],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();

        // Only handle /api routes
        if (!str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }

        // Skip if already handled by another subscriber
        if ($event->getResponse() !== null) {
            return;
        }

        $throwable = $event->getThrowable();

        // Handle MapRequestPayload validation errors (422)
        if ($throwable instanceof UnprocessableEntityHttpException) {
            $previous = $throwable->getPrevious();
            if ($previous instanceof ValidationFailedException) {
                $event->setResponse($this->createValidationResponse($previous));
                return;
            }
        }

        // Handle any other HttpException as JSON
        if ($throwable instanceof HttpExceptionInterface) {
            $event->setResponse(new JsonResponse([
                'error' => [
                    'message' => $throwable->getMessage(),
                    'code' => 'HTTP_ERROR',
                ],
            ], $throwable->getStatusCode()));
            return;
        }

        // Catch-all: any unhandled exception becomes a 500 JSON response
        $event->setResponse(new JsonResponse([
            'error' => [
                'message' => 'Error interno del servidor',
                'code' => 'INTERNAL_ERROR',
            ],
        ], Response::HTTP_INTERNAL_SERVER_ERROR));
    }

    private function createValidationResponse(ValidationFailedException $exception): JsonResponse
    {
        $details = [];

        foreach ($exception->getViolations() as $violation) {
            $field = $violation->getPropertyPath();
            $details[$field][] = $violation->getMessage();
        }

        return new JsonResponse([
            'error' => [
                'message' => 'Validation failed',
                'code' => 'VALIDATION_ERROR',
                'details' => $details,
            ],
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
