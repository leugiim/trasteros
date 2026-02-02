<?php

declare(strict_types=1);

/**
 * Script para crear datos de prueba en la base de datos.
 *
 * Uso: php scripts/seeds.php
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\Kernel;
use App\Users\Application\Command\CreateUser\CreateUserCommand;
use App\Users\Domain\Model\UserRole;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

// Cargar variables de entorno
$dotenv = new Dotenv();
$dotenv->loadEnv(dirname(__DIR__) . '/.env');

// Inicializar el kernel de Symfony
$kernel = new Kernel($_ENV['APP_ENV'] ?? 'dev', (bool) ($_ENV['APP_DEBUG'] ?? true));
$kernel->boot();

$container = $kernel->getContainer();

/** @var MessageBusInterface $commandBus */
$commandBus = $container->get(MessageBusInterface::class);

echo "Iniciando seeds...\n";

$command = new CreateUserCommand(
    nombre: 'Admin Test',
    email: 'admin@trasteros.test',
    password: 'password123',
    rol: UserRole::ADMIN->value,
    activo: true
);

$envelope = $commandBus->dispatch($command);

/** @var HandledStamp $handledStamp */
$handledStamp = $envelope->last(HandledStamp::class);
$userResponse = $handledStamp->getResult();

echo "Usuario creado:\n";
echo "  - ID: {$userResponse->id}\n";
echo "  - Nombre: {$userResponse->nombre}\n";
echo "  - Email: {$userResponse->email}\n";
echo "  - Rol: {$userResponse->rol}\n";
echo "  - Password: password123\n";

echo "\nSeeds completados.\n";
