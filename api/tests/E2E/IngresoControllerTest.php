<?php

declare(strict_types=1);

namespace App\Tests\E2E;

class IngresoControllerTest extends ApiTestCase
{
    private int $contratoId;
    private int $trasteroId;
    private int $localId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authenticate();
        $this->createRequiredEntities();
    }

    private function createRequiredEntities(): void
    {
        // Create direccion
        $direccionResponse = $this->post('/api/direcciones', [
            'nombreVia' => 'Ingreso Test Street',
            'codigoPostal' => '28040',
            'ciudad' => 'Madrid',
            'provincia' => 'Madrid',
            'pais' => 'Espana',
        ]);

        // Create local
        $localResponse = $this->post('/api/locales', [
            'nombre' => 'Ingreso Test Local',
            'direccionId' => $direccionResponse['data']['id'],
            'superficieTotal' => 500.0,
        ]);

        $this->localId = $localResponse['data']['id'];

        // Create trastero
        $trasteroResponse = $this->post('/api/trasteros', [
            'localId' => $this->localId,
            'numero' => 'IT-01',
            'superficie' => 10.0,
            'precioMensual' => 100.0,
            'estado' => 'disponible',
        ]);

        $this->trasteroId = $trasteroResponse['data']['id'];

        // Create cliente
        $clienteResponse = $this->post('/api/clientes', [
            'nombre' => 'Ingreso',
            'apellidos' => 'Test Cliente',
            'dniNie' => '66666666W',
            'email' => 'ingresotest@example.com',
            'telefono' => '+34612345678',
        ]);

        // Create contrato
        $contratoResponse = $this->post('/api/contratos', [
            'trasteroId' => $this->trasteroId,
            'clienteId' => $clienteResponse['data']['id'],
            'fechaInicio' => '2024-01-01',
            'precioMensual' => 100.0,
            'fianza' => 200.0,
        ]);

        $this->contratoId = $contratoResponse['data']['id'];
    }

    public function testListIngresos(): void
    {
        $response = $this->get('/api/ingresos');

        $this->assertResponseStatusCode(200, $response);
        $this->assertArrayHasKey('data', $response['data']);
        $this->assertArrayHasKey('meta', $response['data']);
    }

    public function testListIngresosWithoutAuth(): void
    {
        $this->authToken = null;
        $response = $this->get('/api/ingresos');

        $this->assertResponseStatusCode(401, $response);
    }

    public function testCreateIngreso(): void
    {
        $response = $this->post('/api/ingresos', [
            'contratoId' => $this->contratoId,
            'concepto' => 'Mensualidad Enero 2024',
            'importe' => 100.0,
            'fechaPago' => '2024-01-05',
            'categoria' => 'alquiler',
            'metodoPago' => 'transferencia',
        ]);

        $this->assertResponseStatusCode(201, $response);
        $this->assertEquals('Mensualidad Enero 2024', $response['data']['concepto']);
        $this->assertEquals(100.0, $response['data']['importe']);
        $this->assertEquals('alquiler', $response['data']['categoria']);
    }

    public function testCreateIngresoWithNonExistentContrato(): void
    {
        $response = $this->post('/api/ingresos', [
            'contratoId' => 99999,
            'concepto' => 'Test',
            'importe' => 100.0,
            'fechaPago' => '2024-01-05',
            'categoria' => 'alquiler',
            'metodoPago' => 'efectivo',
        ]);

        $this->assertResponseStatusCode(400, $response);
        $this->assertHasError($response, 'VALIDATION_ERROR');
    }

    public function testShowIngreso(): void
    {
        // First create an ingreso
        $createResponse = $this->post('/api/ingresos', [
            'contratoId' => $this->contratoId,
            'concepto' => 'Show Test Ingreso',
            'importe' => 150.0,
            'fechaPago' => '2024-02-05',
            'categoria' => 'alquiler',
            'metodoPago' => 'bizum',
        ]);

        $ingresoId = $createResponse['data']['id'];

        // Then fetch it
        $response = $this->get('/api/ingresos/' . $ingresoId);

        $this->assertResponseStatusCode(200, $response);
        $this->assertEquals('Show Test Ingreso', $response['data']['concepto']);
    }

    public function testShowNonExistentIngreso(): void
    {
        $response = $this->get('/api/ingresos/99999');

        $this->assertResponseStatusCode(404, $response);
        $this->assertHasError($response, 'INGRESO_NOT_FOUND');
    }

    public function testUpdateIngreso(): void
    {
        // First create an ingreso
        $createResponse = $this->post('/api/ingresos', [
            'contratoId' => $this->contratoId,
            'concepto' => 'Update Test',
            'importe' => 100.0,
            'fechaPago' => '2024-03-05',
            'categoria' => 'alquiler',
            'metodoPago' => 'efectivo',
        ]);

        $ingresoId = $createResponse['data']['id'];

        // Then update it
        $response = $this->put('/api/ingresos/' . $ingresoId, [
            'contratoId' => $this->contratoId,
            'concepto' => 'Updated Concepto',
            'importe' => 120.0,
            'fechaPago' => '2024-03-10',
            'categoria' => 'otros',
            'metodoPago' => 'tarjeta',
        ]);

        $this->assertResponseStatusCode(200, $response);
        $this->assertEquals('Updated Concepto', $response['data']['concepto']);
        $this->assertEquals(120.0, $response['data']['importe']);
    }

    public function testDeleteIngreso(): void
    {
        // First create an ingreso
        $createResponse = $this->post('/api/ingresos', [
            'contratoId' => $this->contratoId,
            'concepto' => 'Delete Test',
            'importe' => 100.0,
            'fechaPago' => '2024-04-05',
            'categoria' => 'alquiler',
            'metodoPago' => 'domiciliacion',
        ]);

        $ingresoId = $createResponse['data']['id'];

        // Then delete it
        $response = $this->delete('/api/ingresos/' . $ingresoId);

        $this->assertResponseStatusCode(204, $response);

        // Verify it's deleted
        $showResponse = $this->get('/api/ingresos/' . $ingresoId);
        $this->assertResponseStatusCode(404, $showResponse);
    }

    public function testListIngresosByContrato(): void
    {
        // Create an ingreso
        $this->post('/api/ingresos', [
            'contratoId' => $this->contratoId,
            'concepto' => 'By Contrato Test',
            'importe' => 100.0,
            'fechaPago' => '2024-05-05',
            'categoria' => 'alquiler',
            'metodoPago' => 'transferencia',
        ]);

        $response = $this->get('/api/ingresos/contrato/' . $this->contratoId);

        $this->assertResponseStatusCode(200, $response);
        $this->assertArrayHasKey('data', $response['data']);
        $this->assertGreaterThanOrEqual(1, count($response['data']['data']));
    }

    public function testListIngresosByTrastero(): void
    {
        // Create an ingreso
        $this->post('/api/ingresos', [
            'contratoId' => $this->contratoId,
            'concepto' => 'By Trastero Test',
            'importe' => 100.0,
            'fechaPago' => '2024-06-05',
            'categoria' => 'alquiler',
            'metodoPago' => 'efectivo',
        ]);

        $response = $this->get('/api/ingresos/trastero/' . $this->trasteroId);

        $this->assertResponseStatusCode(200, $response);
        $this->assertArrayHasKey('data', $response['data']);
    }

    public function testListIngresosByLocal(): void
    {
        // Create an ingreso
        $this->post('/api/ingresos', [
            'contratoId' => $this->contratoId,
            'concepto' => 'By Local Test',
            'importe' => 100.0,
            'fechaPago' => '2024-07-05',
            'categoria' => 'alquiler',
            'metodoPago' => 'bizum',
        ]);

        $response = $this->get('/api/ingresos/local/' . $this->localId);

        $this->assertResponseStatusCode(200, $response);
        $this->assertArrayHasKey('data', $response['data']);
    }
}
