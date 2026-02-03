<?php

declare(strict_types=1);

namespace App\Tests\E2E;

class DashboardControllerTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->authenticate();
    }

    public function testGetDashboardStats(): void
    {
        $response = $this->get('/api/dashboard/stats');

        $this->assertResponseStatusCode(200, $response);
        $this->assertArrayHasKey('trasteros', $response['data']);
        $this->assertArrayHasKey('contratos', $response['data']);
        $this->assertArrayHasKey('entidades', $response['data']);
        $this->assertArrayHasKey('financiero', $response['data']);

        // Check trasteros structure
        $this->assertArrayHasKey('total', $response['data']['trasteros']);
        $this->assertArrayHasKey('disponibles', $response['data']['trasteros']);
        $this->assertArrayHasKey('ocupados', $response['data']['trasteros']);
        $this->assertArrayHasKey('tasaOcupacion', $response['data']['trasteros']);

        // Check contratos structure
        $this->assertArrayHasKey('activos', $response['data']['contratos']);
        $this->assertArrayHasKey('total', $response['data']['contratos']);
        $this->assertArrayHasKey('proximosAVencer', $response['data']['contratos']);
        $this->assertArrayHasKey('fianzasPendientes', $response['data']['contratos']);

        // Check entidades structure
        $this->assertArrayHasKey('clientes', $response['data']['entidades']);
        $this->assertArrayHasKey('locales', $response['data']['entidades']);

        // Check financiero structure
        $this->assertArrayHasKey('ingresosMes', $response['data']['financiero']);
        $this->assertArrayHasKey('gastosMes', $response['data']['financiero']);
        $this->assertArrayHasKey('balanceMes', $response['data']['financiero']);
    }

    public function testGetDashboardStatsWithoutAuth(): void
    {
        $this->authToken = null;
        $response = $this->get('/api/dashboard/stats');

        $this->assertResponseStatusCode(401, $response);
    }

    public function testGetRentabilidad(): void
    {
        $response = $this->get('/api/dashboard/rentabilidad');

        $this->assertResponseStatusCode(200, $response);
        $this->assertArrayHasKey('data', $response['data']);
        $this->assertIsArray($response['data']['data']);
    }

    public function testGetRentabilidadWithDateRange(): void
    {
        $response = $this->get('/api/dashboard/rentabilidad?fechaInicio=2024-01-01&fechaFin=2024-12-31');

        $this->assertResponseStatusCode(200, $response);
        $this->assertArrayHasKey('data', $response['data']);
    }

    public function testGetRentabilidadWithoutAuth(): void
    {
        $this->authToken = null;
        $response = $this->get('/api/dashboard/rentabilidad');

        $this->assertResponseStatusCode(401, $response);
    }

    public function testDashboardStatsWithData(): void
    {
        // Create some data to have meaningful stats

        // Create direccion
        $direccionResponse = $this->post('/api/direcciones', [
            'nombreVia' => 'Dashboard Test Street',
            'codigoPostal' => '28070',
            'ciudad' => 'Madrid',
            'provincia' => 'Madrid',
            'pais' => 'Espana',
        ]);

        // Create local
        $localResponse = $this->post('/api/locales', [
            'nombre' => 'Dashboard Test Local',
            'direccionId' => $direccionResponse['data']['id'],
            'superficieTotal' => 500.0,
        ]);

        // Create trasteros
        $this->post('/api/trasteros', [
            'localId' => $localResponse['data']['id'],
            'numero' => 'DT-01',
            'superficie' => 10.0,
            'precioMensual' => 100.0,
            'estado' => 'disponible',
        ]);

        $trasteroResponse = $this->post('/api/trasteros', [
            'localId' => $localResponse['data']['id'],
            'numero' => 'DT-02',
            'superficie' => 15.0,
            'precioMensual' => 150.0,
            'estado' => 'disponible',
        ]);

        // Create cliente
        $clienteResponse = $this->post('/api/clientes', [
            'nombre' => 'Dashboard',
            'apellidos' => 'Test Cliente',
            'dniNie' => '77777777M',
            'email' => 'dashboardtest@example.com',
            'telefono' => '+34612345678',
        ]);

        // Create contrato
        $this->post('/api/contratos', [
            'trasteroId' => $trasteroResponse['data']['id'],
            'clienteId' => $clienteResponse['data']['id'],
            'fechaInicio' => '2024-01-01',
            'precioMensual' => 150.0,
            'fianza' => 300.0,
            'fianzaPagada' => false,
        ]);

        // Create gasto
        $this->post('/api/gastos', [
            'localId' => $localResponse['data']['id'],
            'concepto' => 'Dashboard Test Gasto',
            'importe' => 50.0,
            'fecha' => date('Y-m-d'),
            'categoria' => 'suministros',
            'metodoPago' => 'transferencia',
        ]);

        // Now check stats
        $response = $this->get('/api/dashboard/stats');

        $this->assertResponseStatusCode(200, $response);
        $this->assertGreaterThanOrEqual(1, $response['data']['trasteros']['total']);
        $this->assertGreaterThanOrEqual(1, $response['data']['entidades']['locales']);
        $this->assertGreaterThanOrEqual(1, $response['data']['entidades']['clientes']);
    }
}
