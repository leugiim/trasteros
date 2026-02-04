<?php

declare(strict_types=1);

/**
 * Script para crear datos de prueba en la base de datos.
 *
 * Uso: php scripts/seeds.php [--env=dev|test]
 *
 * Este script es un wrapper que ejecuta el comando Symfony app:database:seeds.
 * La lógica de seeds está centralizada en DatabaseSeedsCommand.
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Dotenv\Dotenv;

// Parsear argumentos CLI
$env = 'dev';
foreach ($argv as $arg) {
    if (str_starts_with($arg, '--env=')) {
        $env = substr($arg, 6);
    }
}

// Cargar variables de entorno solo si no están ya cargadas
if (!isset($_ENV['APP_ENV'])) {
    $dotenv = new Dotenv();
    $dotenv->loadEnv(dirname(__DIR__) . '/.env');
}

// Permitir override del entorno
$_ENV['APP_ENV'] = $env;
$_SERVER['APP_ENV'] = $env;

// Inicializar el kernel de Symfony
$kernel = new Kernel($env, (bool) ($_ENV['APP_DEBUG'] ?? true));
$kernel->boot();

// Ejecutar el comando de seeds
$application = new Application($kernel);
$application->setAutoExit(true);

$input = new ArgvInput(['console', 'app:database:seeds']);
$output = new ConsoleOutput();

exit($application->run($input, $output));
