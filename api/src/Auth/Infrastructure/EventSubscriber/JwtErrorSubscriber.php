<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\EventSubscriber;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTExpiredEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTInvalidEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTNotFoundEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Makes the firewall's 401s (missing, invalid or expired JWT, inactive user)
 * use the API error format and the same code AuthSubscriber has always
 * returned: {"error": {"message": ..., "code": "UNAUTHORIZED"}}.
 */
final class JwtErrorSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            Events::JWT_NOT_FOUND => 'onJwtNotFound',
            Events::JWT_INVALID => 'onJwtInvalid',
            Events::JWT_EXPIRED => 'onJwtExpired',
        ];
    }

    public function onJwtNotFound(JWTNotFoundEvent $event): void
    {
        $this->setError($event, 'Token de autenticación no proporcionado');
    }

    public function onJwtInvalid(JWTInvalidEvent $event): void
    {
        $this->setError($event, 'Token de autenticación inválido');
    }

    public function onJwtExpired(JWTExpiredEvent $event): void
    {
        $this->setError($event, 'Token de autenticación expirado');
    }

    private function setError(AuthenticationFailureEvent $event, string $message): void
    {
        $event->setResponse(new JsonResponse([
            'error' => [
                'message' => $message,
                'code' => 'UNAUTHORIZED',
            ],
        ], Response::HTTP_UNAUTHORIZED));
    }
}
