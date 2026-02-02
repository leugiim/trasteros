<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\EventSubscriber;

use App\Auth\Domain\Exception\InvalidTokenException;
use App\Auth\Infrastructure\Attribute\Auth;
use App\Users\Domain\Model\UserEmail;
use App\Users\Domain\Repository\UserRepositoryInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class AuthSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly JWTTokenManagerInterface $jwtManager,
        private readonly UserRepositoryInterface $userRepository
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => ['onKernelController', 10],
        ];
    }

    public function onKernelController(ControllerEvent $event): void
    {
        $controller = $event->getController();

        if (is_array($controller)) {
            $controllerObject = $controller[0];
            $methodName = $controller[1];
        } else {
            return;
        }

        $authAttribute = $this->getAuthAttribute($controllerObject, $methodName);

        if ($authAttribute === null) {
            return;
        }

        $request = $event->getRequest();
        $authHeader = $request->headers->get('Authorization');

        try {
            if ($authHeader === null || !str_starts_with($authHeader, 'Bearer ')) {
                throw InvalidTokenException::missing();
            }

            $token = substr($authHeader, 7);
            $payload = $this->jwtManager->parse($token);

            if ($payload === false || !isset($payload['username'])) {
                throw InvalidTokenException::invalid();
            }

            $email = UserEmail::fromString($payload['username']);
            $user = $this->userRepository->findByEmail($email);

            if ($user === null) {
                throw InvalidTokenException::invalid();
            }

            if (!$user->isActivo()) {
                throw InvalidTokenException::invalid();
            }

            // Verificar roles si se especificaron
            if (!empty($authAttribute->roles)) {
                $userRole = $user->rol()->value;
                if (!in_array($userRole, $authAttribute->roles, true)) {
                    $event->setController(function () {
                        return new JsonResponse([
                            'error' => [
                                'message' => 'No tienes permisos para acceder a este recurso',
                                'code' => 'FORBIDDEN',
                            ],
                        ], Response::HTTP_FORBIDDEN);
                    });
                    return;
                }
            }

            // Añadir usuario al request para uso posterior
            $request->attributes->set('authenticated_user', $user);
            $request->attributes->set('authenticated_user_id', $user->id()->value);
        } catch (InvalidTokenException $e) {
            $event->setController(function () use ($e) {
                return new JsonResponse([
                    'error' => [
                        'message' => $e->getMessage(),
                        'code' => 'UNAUTHORIZED',
                    ],
                ], Response::HTTP_UNAUTHORIZED);
            });
        } catch (\Exception) {
            $event->setController(function () {
                return new JsonResponse([
                    'error' => [
                        'message' => 'Token de autenticación inválido',
                        'code' => 'UNAUTHORIZED',
                    ],
                ], Response::HTTP_UNAUTHORIZED);
            });
        }
    }

    private function getAuthAttribute(object $controller, string $methodName): ?Auth
    {
        $reflectionClass = new \ReflectionClass($controller);

        // Primero verificar el método
        if ($reflectionClass->hasMethod($methodName)) {
            $reflectionMethod = $reflectionClass->getMethod($methodName);
            $methodAttributes = $reflectionMethod->getAttributes(Auth::class);

            if (!empty($methodAttributes)) {
                return $methodAttributes[0]->newInstance();
            }
        }

        // Luego verificar la clase
        $classAttributes = $reflectionClass->getAttributes(Auth::class);
        if (!empty($classAttributes)) {
            return $classAttributes[0]->newInstance();
        }

        return null;
    }
}
