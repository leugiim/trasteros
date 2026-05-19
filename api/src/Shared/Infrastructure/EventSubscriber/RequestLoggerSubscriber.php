<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class RequestLoggerSubscriber implements EventSubscriberInterface
{
    private const SENSITIVE_BODY_FIELDS = ['password', 'currentPassword', 'newPassword', 'token', 'refreshToken'];
    private const SENSITIVE_HEADERS = ['authorization', 'x-auth-token'];

    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onRequest', 1],
            KernelEvents::RESPONSE => ['onResponse', 1],
        ];
    }

    public function onRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        if (!str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }

        if ($request->getPathInfo() === '/api/health') {
            return;
        }

        $body = [];
        $content = $request->getContent();
        if ($content) {
            $decoded = json_decode($content, true);
            if (is_array($decoded)) {
                $body = $this->maskSensitiveFields($decoded);
            }
        }

        $headers = $this->maskSensitiveHeaders($request->headers->all());

        $this->logger->info('→ {method} {path}', [
            'method' => $request->getMethod(),
            'path' => $request->getPathInfo(),
            'query' => $request->query->all(),
            'body' => $body,
            'headers' => $headers,
        ]);
    }

    public function onResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $response = $event->getResponse();

        if (!str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }

        if ($request->getPathInfo() === '/api/health') {
            return;
        }

        $body = [];
        $content = $response->getContent();
        if ($content) {
            $decoded = json_decode($content, true);
            if (is_array($decoded)) {
                $body = $this->maskSensitiveFields($decoded);
            }
        }

        $this->logger->info('← {status} {method} {path}', [
            'method' => $request->getMethod(),
            'path' => $request->getPathInfo(),
            'status' => $response->getStatusCode(),
            'body' => $body,
        ]);
    }

    private function isDebug(): bool
    {
        return filter_var($_ENV['DEBUG_SENSITIVE_LOGS'] ?? 'false', FILTER_VALIDATE_BOOLEAN);
    }

    private function maskSensitiveFields(array $data): array
    {
        if ($this->isDebug()) {
            return $data;
        }

        foreach ($data as $key => $value) {
            if (in_array($key, self::SENSITIVE_BODY_FIELDS, true)) {
                $data[$key] = '***';
            } elseif (is_array($value)) {
                $data[$key] = $this->maskSensitiveFields($value);
            }
        }

        return $data;
    }

    private function maskSensitiveHeaders(array $headers): array
    {
        if ($this->isDebug()) {
            return $headers;
        }

        foreach ($headers as $name => $value) {
            if (in_array(strtolower($name), self::SENSITIVE_HEADERS, true)) {
                $headers[$name] = ['***'];
            }
        }

        return $headers;
    }
}
