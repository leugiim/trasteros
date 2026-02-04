<?php

declare(strict_types=1);

/**
 * Script para crear datos de prueba en la base de datos.
 *
 * Uso: php scripts/seeds.php
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\Cliente\Application\Command\CreateCliente\CreateClienteCommand;
use App\Contrato\Application\Command\CreateContrato\CreateContratoCommand;
use App\Direccion\Application\Command\CreateDireccion\CreateDireccionCommand;
use App\Gasto\Application\Command\CreateGasto\CreateGastoCommand;
use App\Ingreso\Application\Command\CreateIngreso\CreateIngresoCommand;
use App\Kernel;
use App\Local\Application\Command\CreateLocal\CreateLocalCommand;
use App\Prestamo\Application\Command\CreatePrestamo\CreatePrestamoCommand;
use App\Trastero\Application\Command\CreateTrastero\CreateTrasteroCommand;
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

echo "Iniciando seeds...\n\n";

// Helper function para despachar comandos con manejo de errores
function dispatchCommand(MessageBusInterface $commandBus, object $command): ?object
{
    try {
        $envelope = $commandBus->dispatch($command);
        $handledStamp = $envelope->last(HandledStamp::class);
        return $handledStamp->getResult();
    } catch (\Exception $e) {
        echo "  [ERROR] {$e->getMessage()}\n";
        return null;
    }
}

// ============================================================================
// USERS - 2 registros
// ============================================================================
echo "=== USERS ===\n";

$userCommands = [
    new CreateUserCommand(
        nombre: 'Admin Test',
        email: 'admin@trasteros.test',
        password: 'password123',
        rol: UserRole::ADMIN->value,
        activo: true
    ),
    new CreateUserCommand(
        nombre: 'Gestor Test',
        email: 'gestor@trasteros.test',
        password: 'password123',
        rol: UserRole::GESTOR->value,
        activo: true
    ),
];

foreach ($userCommands as $command) {
    $response = dispatchCommand($commandBus, $command);
    if ($response) {
        echo "Usuario creado: {$response->nombre} ({$response->email}) - Rol: {$response->rol}\n";
    }
}

// ============================================================================
// DIRECCIONES - 2 registros
// ============================================================================
echo "\n=== DIRECCIONES ===\n";

$direccionCommands = [
    new CreateDireccionCommand(
        nombreVia: 'Gran Via',
        codigoPostal: '28013',
        ciudad: 'Madrid',
        provincia: 'Madrid',
        pais: 'España',
        tipoVia: 'Calle',
        numero: '42',
        piso: '3',
        puerta: 'A',
        latitud: 40.4168,
        longitud: -3.7038
    ),
    new CreateDireccionCommand(
        nombreVia: 'Diagonal',
        codigoPostal: '08028',
        ciudad: 'Barcelona',
        provincia: 'Barcelona',
        pais: 'España',
        tipoVia: 'Avenida',
        numero: '123',
        piso: null,
        puerta: null,
        latitud: 41.3851,
        longitud: 2.1734
    ),
];

$direccionIds = [];
foreach ($direccionCommands as $command) {
    $response = dispatchCommand($commandBus, $command);
    if ($response) {
        $direccionIds[] = $response->id;
        echo "Direccion creada: {$response->tipoVia} {$response->nombreVia}, {$response->ciudad}\n";
    }
}

if (count($direccionIds) < 2) {
    echo "[ERROR] No se pudieron crear suficientes direcciones. Abortando seeds.\n";
    exit(1);
}

// ============================================================================
// LOCALES - 2 registros
// ============================================================================
echo "\n=== LOCALES ===\n";

$localCommands = [
    new CreateLocalCommand(
        nombre: 'Local Madrid Centro',
        direccionId: $direccionIds[0],
        superficieTotal: 500.0,
        numeroTrasteros: 20,
        fechaCompra: '2024-01-15',
        precioCompra: 250000.0,
        referenciaCatastral: '1234567VK1234A0001PL',
        valorCatastral: 280000.0
    ),
    new CreateLocalCommand(
        nombre: 'Local Barcelona Diagonal',
        direccionId: $direccionIds[1],
        superficieTotal: 750.0,
        numeroTrasteros: 30,
        fechaCompra: '2023-11-20',
        precioCompra: 350000.0,
        referenciaCatastral: '9876543VK9876B0002PL',
        valorCatastral: 400000.0
    ),
];

$localIds = [];
foreach ($localCommands as $command) {
    $response = dispatchCommand($commandBus, $command);
    if ($response) {
        $localIds[] = $response->id;
        echo "Local creado: {$response->nombre} - {$response->superficieTotal}m²\n";
    }
}

if (count($localIds) < 2) {
    echo "[ERROR] No se pudieron crear suficientes locales. Abortando seeds.\n";
    exit(1);
}

// ============================================================================
// CLIENTES - 2 registros
// ============================================================================
echo "\n=== CLIENTES ===\n";

$clienteCommands = [
    new CreateClienteCommand(
        nombre: 'Juan',
        apellidos: 'Garcia Lopez',
        dniNie: '12345678Z',
        email: 'juan.garcia@example.com',
        telefono: '666111222',
        activo: true
    ),
    new CreateClienteCommand(
        nombre: 'Maria',
        apellidos: 'Rodriguez Martinez',
        dniNie: '87654321X',
        email: 'maria.rodriguez@example.com',
        telefono: '666333444',
        activo: true
    ),
];

$clienteIds = [];
foreach ($clienteCommands as $command) {
    $response = dispatchCommand($commandBus, $command);
    if ($response) {
        $clienteIds[] = $response->id;
        echo "Cliente creado: {$response->nombre} {$response->apellidos} ({$response->dniNie})\n";
    }
}

if (count($clienteIds) < 2) {
    echo "[ERROR] No se pudieron crear suficientes clientes. Abortando seeds.\n";
    exit(1);
}

// ============================================================================
// TRASTEROS - 2 registros
// ============================================================================
echo "\n=== TRASTEROS ===\n";

$trasteroCommands = [
    new CreateTrasteroCommand(
        localId: $localIds[0],
        numero: 'T-101',
        superficie: 10.5,
        precioMensual: 85.0,
        nombre: 'Trastero pequeño',
        estado: 'disponible'
    ),
    new CreateTrasteroCommand(
        localId: $localIds[0],
        numero: 'T-102',
        superficie: 15.0,
        precioMensual: 120.0,
        nombre: 'Trastero mediano',
        estado: 'disponible'
    ),
];

$trasteroIds = [];
foreach ($trasteroCommands as $command) {
    $response = dispatchCommand($commandBus, $command);
    if ($response) {
        $trasteroIds[] = $response->id;
        echo "Trastero creado: {$response->numero} - {$response->superficie}m² - {$response->precioMensual}€/mes\n";
    }
}

if (count($trasteroIds) < 2) {
    echo "[ERROR] No se pudieron crear suficientes trasteros. Abortando seeds.\n";
    exit(1);
}

// ============================================================================
// CONTRATOS - 2 registros
// ============================================================================
echo "\n=== CONTRATOS ===\n";

$contratoCommands = [
    new CreateContratoCommand(
        trasteroId: $trasteroIds[0],
        clienteId: $clienteIds[0],
        fechaInicio: '2025-01-01',
        precioMensual: 85.0,
        fechaFin: null,
        fianza: 170.0,
        fianzaPagada: true,
        estado: 'activo'
    ),
    new CreateContratoCommand(
        trasteroId: $trasteroIds[1],
        clienteId: $clienteIds[1],
        fechaInicio: '2025-01-15',
        precioMensual: 120.0,
        fechaFin: null,
        fianza: 240.0,
        fianzaPagada: true,
        estado: 'activo'
    ),
];

$contratoIds = [];
foreach ($contratoCommands as $command) {
    $response = dispatchCommand($commandBus, $command);
    if ($response) {
        $contratoIds[] = $response->id;
        echo "Contrato creado: ID {$response->id} - {$response->precioMensual}€/mes - Estado: {$response->estado}\n";
    }
}

if (count($contratoIds) < 2) {
    echo "[ERROR] No se pudieron crear suficientes contratos. Abortando seeds.\n";
    exit(1);
}

// ============================================================================
// GASTOS - 2 registros
// ============================================================================
echo "\n=== GASTOS ===\n";

$gastoCommands = [
    new CreateGastoCommand(
        localId: $localIds[0],
        concepto: 'Electricidad enero',
        importe: 150.50,
        fecha: '2025-01-31',
        categoria: 'suministros',
        descripcion: 'Factura electricidad mensual',
        metodoPago: 'transferencia'
    ),
    new CreateGastoCommand(
        localId: $localIds[0],
        concepto: 'Seguro local anual',
        importe: 850.0,
        fecha: '2025-01-10',
        categoria: 'seguros',
        descripcion: 'Seguro multirriesgo local',
        metodoPago: 'domiciliacion'
    ),
];

foreach ($gastoCommands as $command) {
    $response = dispatchCommand($commandBus, $command);
    if ($response) {
        echo "Gasto creado: {$response->concepto} - {$response->importe}€ - {$response->categoria}\n";
    }
}

// ============================================================================
// INGRESOS - 2 registros
// ============================================================================
echo "\n=== INGRESOS ===\n";

$ingresoCommands = [
    new CreateIngresoCommand(
        contratoId: $contratoIds[0],
        concepto: 'Mensualidad enero 2025',
        importe: 85.0,
        fechaPago: '2025-01-05',
        categoria: 'mensualidad',
        metodoPago: 'transferencia'
    ),
    new CreateIngresoCommand(
        contratoId: $contratoIds[1],
        concepto: 'Fianza',
        importe: 240.0,
        fechaPago: '2025-01-15',
        categoria: 'fianza',
        metodoPago: 'efectivo'
    ),
];

foreach ($ingresoCommands as $command) {
    $response = dispatchCommand($commandBus, $command);
    if ($response) {
        echo "Ingreso creado: {$response->concepto} - {$response->importe}€ - {$response->categoria}\n";
    }
}

// ============================================================================
// PRESTAMOS - 2 registros
// ============================================================================
echo "\n=== PRESTAMOS ===\n";

$prestamoCommands = [
    new CreatePrestamoCommand(
        localId: $localIds[0],
        capitalSolicitado: 200000.0,
        totalADevolver: 250000.0,
        fechaConcesion: '2024-01-15',
        entidadBancaria: 'Banco Santander',
        numeroPrestamo: 'PRE-2024-001',
        tipoInteres: 3.5,
        estado: 'activo'
    ),
    new CreatePrestamoCommand(
        localId: $localIds[1],
        capitalSolicitado: 300000.0,
        totalADevolver: 375000.0,
        fechaConcesion: '2023-11-20',
        entidadBancaria: 'BBVA',
        numeroPrestamo: 'PRE-2023-045',
        tipoInteres: 3.8,
        estado: 'activo'
    ),
];

foreach ($prestamoCommands as $command) {
    $response = dispatchCommand($commandBus, $command);
    if ($response) {
        echo "Prestamo creado: {$response->numeroPrestamo} - {$response->capitalSolicitado}€ - {$response->entidadBancaria}\n";
    }
}

echo "\n===========================================\n";
echo "Seeds completados exitosamente.\n";
echo "===========================================\n";
