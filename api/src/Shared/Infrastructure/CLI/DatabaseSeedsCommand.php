<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\CLI;

use App\Cliente\Application\Command\CreateCliente\CreateClienteCommand;
use App\Contrato\Application\Command\CreateContrato\CreateContratoCommand;
use App\Direccion\Application\Command\CreateDireccion\CreateDireccionCommand;
use App\Gasto\Application\Command\CreateGasto\CreateGastoCommand;
use App\Ingreso\Application\Command\CreateIngreso\CreateIngresoCommand;
use App\Local\Application\Command\CreateLocal\CreateLocalCommand;
use App\Prestamo\Application\Command\CreatePrestamo\CreatePrestamoCommand;
use App\Trastero\Application\Command\CreateTrastero\CreateTrasteroCommand;
use App\Users\Application\Command\CreateUser\CreateUserCommand;
use App\Users\Domain\Model\UserRole;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

#[AsCommand(
    name: 'app:database:seeds',
    description: 'Seed the database with test data'
)]
final class DatabaseSeedsCommand extends Command
{
    public function __construct(
        private readonly MessageBusInterface $commandBus
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Iniciando seeds de base de datos');

        try {
            // ====================================================================
            // USERS - 2 registros
            // ====================================================================
            $io->section('USERS');

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
                $response = $this->dispatchCommand($command);
                if ($response) {
                    $io->writeln("Usuario creado: {$response->nombre} ({$response->email}) - Rol: {$response->rol}");
                }
            }

            // ====================================================================
            // DIRECCIONES - 2 registros
            // ====================================================================
            $io->section('DIRECCIONES');

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
                $response = $this->dispatchCommand($command);
                if ($response) {
                    $direccionIds[] = $response->id;
                    $io->writeln("Direccion creada: {$response->tipoVia} {$response->nombreVia}, {$response->ciudad}");
                }
            }

            if (count($direccionIds) < 2) {
                $io->error('No se pudieron crear suficientes direcciones. Abortando seeds.');
                return Command::FAILURE;
            }

            // ====================================================================
            // LOCALES - 2 registros
            // ====================================================================
            $io->section('LOCALES');

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
                $response = $this->dispatchCommand($command);
                if ($response) {
                    $localIds[] = $response->id;
                    $io->writeln("Local creado: {$response->nombre} - {$response->superficieTotal}m²");
                }
            }

            if (count($localIds) < 2) {
                $io->error('No se pudieron crear suficientes locales. Abortando seeds.');
                return Command::FAILURE;
            }

            // ====================================================================
            // CLIENTES - 2 registros
            // ====================================================================
            $io->section('CLIENTES');

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
                $response = $this->dispatchCommand($command);
                if ($response) {
                    $clienteIds[] = $response->id;
                    $io->writeln("Cliente creado: {$response->nombre} {$response->apellidos} ({$response->dniNie})");
                }
            }

            if (count($clienteIds) < 2) {
                $io->error('No se pudieron crear suficientes clientes. Abortando seeds.');
                return Command::FAILURE;
            }

            // ====================================================================
            // TRASTEROS - 2 registros
            // ====================================================================
            $io->section('TRASTEROS');

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
                $response = $this->dispatchCommand($command);
                if ($response) {
                    $trasteroIds[] = $response->id;
                    $io->writeln("Trastero creado: {$response->numero} - {$response->superficie}m² - {$response->precioMensual}€/mes");
                }
            }

            if (count($trasteroIds) < 2) {
                $io->error('No se pudieron crear suficientes trasteros. Abortando seeds.');
                return Command::FAILURE;
            }

            // ====================================================================
            // CONTRATOS - 2 registros
            // ====================================================================
            $io->section('CONTRATOS');

            $contratoCommands = [
                new CreateContratoCommand(
                    trasteroId: $trasteroIds[0],
                    clienteId: $clienteIds[0],
                    fechaInicio: '2025-01-01',
                    precioMensual: 85.0,
                    fechaFin: null,
                    fianza: 170.0,
                    fianzaPagada: true
                ),
                new CreateContratoCommand(
                    trasteroId: $trasteroIds[1],
                    clienteId: $clienteIds[1],
                    fechaInicio: '2025-01-15',
                    precioMensual: 120.0,
                    fechaFin: null,
                    fianza: 240.0,
                    fianzaPagada: true
                ),
            ];

            $contratoIds = [];
            foreach ($contratoCommands as $command) {
                $response = $this->dispatchCommand($command);
                if ($response) {
                    $contratoIds[] = $response->id;
                    $io->writeln("Contrato creado: ID {$response->id} - {$response->precioMensual}€/mes - Estado: {$response->estado}");
                }
            }

            if (count($contratoIds) < 2) {
                $io->error('No se pudieron crear suficientes contratos. Abortando seeds.');
                return Command::FAILURE;
            }

            // ====================================================================
            // GASTOS - 2 registros
            // ====================================================================
            $io->section('GASTOS');

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
                $response = $this->dispatchCommand($command);
                if ($response) {
                    $io->writeln("Gasto creado: {$response->concepto} - {$response->importe}€ - {$response->categoria}");
                }
            }

            // ====================================================================
            // INGRESOS - 2 registros
            // ====================================================================
            $io->section('INGRESOS');

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
                $response = $this->dispatchCommand($command);
                if ($response) {
                    $io->writeln("Ingreso creado: {$response->concepto} - {$response->importe}€ - {$response->categoria}");
                }
            }

            // ====================================================================
            // PRESTAMOS - 2 registros
            // ====================================================================
            $io->section('PRESTAMOS');

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
                $response = $this->dispatchCommand($command);
                if ($response) {
                    $io->writeln("Prestamo creado: {$response->numeroPrestamo} - {$response->capitalSolicitado}€ - {$response->entidadBancaria}");
                }
            }

            $io->success('Seeds completados exitosamente');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Error durante la ejecución de seeds: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function dispatchCommand(object $command): ?object
    {
        try {
            $envelope = $this->commandBus->dispatch($command);
            $handledStamp = $envelope->last(HandledStamp::class);
            return $handledStamp?->getResult();
        } catch (\Exception $e) {
            throw new \RuntimeException("Error dispatching command: {$e->getMessage()}", 0, $e);
        }
    }
}
