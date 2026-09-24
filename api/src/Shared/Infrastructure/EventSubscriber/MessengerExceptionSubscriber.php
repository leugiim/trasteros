<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\EventSubscriber;

use App\Shared\Domain\Exception\AuthenticationError;
use App\Shared\Domain\Exception\ConflictError;
use App\Shared\Domain\Exception\DomainError;
use App\Shared\Domain\Exception\ForbiddenError;
use App\Shared\Domain\Exception\NotFoundError;
use App\Shared\Domain\Exception\ValidationError;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\Exception\HandlerFailedException;

/**
 * Turns domain errors (Shared\Domain\Exception\DomainError) into JSON
 * responses, whether they were thrown directly or wrapped by Messenger's
 * HandlerFailedException. The HTTP status comes from the error's type and
 * the code from the error itself, so a new exception only has to extend the
 * right base class.
 */
final class MessengerExceptionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // Before API Platform and ApiExceptionSubscriber (50, the fallback)
            KernelEvents::EXCEPTION => ['onKernelException', 100],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $error = $this->unwrap($event->getThrowable());

        if (!$error instanceof DomainError) {
            return;
        }

        $this->logger->error('Domain exception: {class} - {message}', [
            'class' => $error::class,
            'message' => $error->getMessage(),
            'exception' => $error,
        ]);

        $event->setResponse($this->createResponse($error));
    }

    private function unwrap(\Throwable $throwable): \Throwable
    {
        if ($throwable instanceof HandlerFailedException) {
            foreach ($throwable->getWrappedExceptions() as $wrapped) {
                return $wrapped;
            }
        }

        return $throwable;
    }

    private function createResponse(DomainError $error): JsonResponse
    {
        if ($error instanceof ValidationError) {
            return new JsonResponse([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => $error->errorCode(),
                    'details' => [
                        $error->field() => [$error->getMessage()],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        $status = match (true) {
            $error instanceof NotFoundError => Response::HTTP_NOT_FOUND,
            $error instanceof ConflictError => Response::HTTP_CONFLICT,
            $error instanceof AuthenticationError => Response::HTTP_UNAUTHORIZED,
            $error instanceof ForbiddenError => Response::HTTP_FORBIDDEN,
            default => Response::HTTP_BAD_REQUEST,
        };

        return new JsonResponse([
            'error' => [
                'message' => $error->getMessage(),
                'code' => $error->errorCode(),
            ],
        ], $status);
    }
}
