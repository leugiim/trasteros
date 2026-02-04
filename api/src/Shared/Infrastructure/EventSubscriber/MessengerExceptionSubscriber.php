<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\Exception\HandlerFailedException;

final class MessengerExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            // Use high priority to catch exceptions before API Platform
            KernelEvents::EXCEPTION => ['onKernelException', 100],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();

        // Only handle HandlerFailedException from Messenger
        if (!$throwable instanceof HandlerFailedException) {
            return;
        }

        // Extract the original exception from HandlerFailedException
        $originalException = $this->extractOriginalException($throwable);

        if ($originalException === null) {
            return;
        }

        // Map domain exceptions to HTTP responses
        $response = $this->createResponseFromException($originalException);

        if ($response !== null) {
            $event->setResponse($response);
        }
    }

    private function extractOriginalException(\Throwable $exception): ?\Throwable
    {
        if (!$exception instanceof HandlerFailedException) {
            return null;
        }

        $exceptions = $exception->getWrappedExceptions();

        // getWrappedExceptions() returns an array, not an iterator
        foreach ($exceptions as $wrappedException) {
            return $wrappedException;
        }

        return null;
    }

    private function createResponseFromException(\Throwable $exception): ?JsonResponse
    {
        $exceptionClass = get_class($exception);

        // NotFound exceptions - 404
        if (str_contains($exceptionClass, 'NotFoundException')) {
            return new JsonResponse([
                'error' => [
                    'message' => $exception->getMessage(),
                    'code' => $this->getErrorCode($exceptionClass),
                ],
            ], Response::HTTP_NOT_FOUND);
        }

        // Duplicated or AlreadyExists exceptions - 409
        if (str_contains($exceptionClass, 'DuplicatedException')
            || str_contains($exceptionClass, 'Duplicated')
            || str_contains($exceptionClass, 'AlreadyExistsException')) {
            return new JsonResponse([
                'error' => [
                    'message' => $exception->getMessage(),
                    'code' => $this->getAlreadyExistsErrorCode($exceptionClass),
                ],
            ], Response::HTTP_CONFLICT);
        }

        // Invalid exceptions - 400
        if (str_contains($exceptionClass, 'InvalidException') || str_contains($exceptionClass, 'Invalid')) {
            return new JsonResponse([
                'error' => [
                    'message' => 'Validation failed',
                    'code' => 'VALIDATION_ERROR',
                    'details' => [
                        $this->getFieldFromException($exceptionClass) => [$exception->getMessage()],
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        // Default: don't handle it here, let Symfony handle it
        return null;
    }

    private function getErrorCode(string $exceptionClass): string
    {
        // Extract entity name from exception class
        // e.g., App\Cliente\Domain\Exception\ClienteNotFoundException -> CLIENTE_NOT_FOUND
        if (preg_match('/([A-Z][a-z]+)NotFoundException$/', $exceptionClass, $matches)) {
            return strtoupper($matches[1]) . '_NOT_FOUND';
        }

        return 'NOT_FOUND';
    }

    private function getAlreadyExistsErrorCode(string $exceptionClass): string
    {
        // Extract entity name from exception class
        // e.g., App\Users\Domain\Exception\UserAlreadyExistsException -> USER_ALREADY_EXISTS
        if (preg_match('/([A-Z][a-z]+)AlreadyExistsException$/', $exceptionClass, $matches)) {
            return strtoupper($matches[1]) . '_ALREADY_EXISTS';
        }

        if (preg_match('/(Duplicated|Invalid)([A-Z][a-z]+)Exception$/', $exceptionClass, $matches)) {
            return 'VALIDATION_ERROR';
        }

        return 'ALREADY_EXISTS';
    }

    private function getFieldFromException(string $exceptionClass): string
    {
        // Extract field name from exception class
        // e.g., InvalidDniNieException -> dniNie
        // e.g., DuplicatedEmailException -> email
        preg_match('/(Invalid|Duplicated)([A-Z][a-zA-Z]+)Exception$/', $exceptionClass, $matches);

        if (count($matches) === 3) {
            return lcfirst($matches[2]);
        }

        return 'field';
    }
}
