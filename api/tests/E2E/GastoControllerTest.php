<?php

declare(strict_types=1);

namespace App\Tests\E2E;

class GastoControllerTest extends ApiTestCase
{
    private int $localId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authenticate();
        $this->createLocalWithDireccion();
    }

    private function createLocalWithDireccion(): void
    {
        // Create direccion
        $direccionResponse = $this->post('/api/direcciones', [
            'nombreVia' => 'Gasto Test Street',
            'codigoPostal' => '28050',
            'ciudad' => 'Madrid',
            'provincia' => 'Madrid',
            'pais' => 'Espana',
        ]);

        if (!isset($direccionResponse['data']['id'])) {
            throw new \RuntimeException('Failed to create direccion for tests: ' . json_encode($direccionResponse));
        }

        // Create local
        $localResponse = $this->post('/api/locales', [
            'nombre' => 'Gasto Test Local',
            'direccionId' => $direccionResponse['data']['id'],
            'superficieTotal' => 500.0,
        ]);

        if (!isset($localResponse['data']['id'])) {
            throw new \RuntimeException('Failed to create local for tests: ' . json_encode($localResponse));
        }

        $this->localId = $localResponse['data']['id'];
    }

    public function testListGastos(): void
    {
        $response = $this->get('/api/gastos');

        $this->assertResponseStatusCode(200, $response);
        $this->assertArrayHasKey('data', $response['data']);
        $this->assertArrayHasKey('meta', $response['data']);
    }

    public function testListGastosWithoutAuth(): void
    {
        $this->authToken = null;
        $response = $this->get('/api/gastos');

        $this->assertResponseStatusCode(401, $response);
    }

    public function testCreateGasto(): void
    {
        $response = $this->post('/api/gastos', [
            'localId' => $this->localId,
            'concepto' => 'Factura electricidad',
            'importe' => 150.0,
            'fecha' => '2024-01-15',
            'categoria' => 'suministros',
            'descripcion' => 'Factura mensual de electricidad',
            'metodoPago' => 'domiciliacion',
        ]);

        $this->assertResponseStatusCode(201, $response);
        $this->assertEquals('Factura electricidad', $response['data']['concepto']);
        $this->assertEquals(150.0, $response['data']['importe']);
        $this->assertEquals('suministros', $response['data']['categoria']);
    }

    public function testCreateGastoWithNonExistentLocal(): void
    {
        $response = $this->post('/api/gastos', [
            'localId' => 99999,
            'concepto' => 'Test',
            'importe' => 100.0,
            'fecha' => '2024-01-15',
            'categoria' => 'suministros',
            'metodoPago' => 'efectivo',
        ]);

        $this->assertResponseStatusCode(404, $response);
        $this->assertHasError($response, 'LOCAL_NOT_FOUND');
    }

    public function testShowGasto(): void
    {
        // First create a gasto
        $createResponse = $this->post('/api/gastos', [
            'localId' => $this->localId,
            'concepto' => 'Show Test Gasto',
            'importe' => 200.0,
            'fecha' => '2024-02-15',
            'categoria' => 'mantenimiento',
            'metodoPago' => 'transferencia',
        ]);

        $gastoId = $createResponse['data']['id'];

        // Then fetch it
        $response = $this->get('/api/gastos/' . $gastoId);

        $this->assertResponseStatusCode(200, $response);
        $this->assertEquals('Show Test Gasto', $response['data']['concepto']);
    }

    public function testShowNonExistentGasto(): void
    {
        $response = $this->get('/api/gastos/99999');

        $this->assertResponseStatusCode(404, $response);
        $this->assertHasError($response, 'GASTO_NOT_FOUND');
    }

    public function testUpdateGasto(): void
    {
        // First create a gasto
        $createResponse = $this->post('/api/gastos', [
            'localId' => $this->localId,
            'concepto' => 'Update Test',
            'importe' => 100.0,
            'fecha' => '2024-03-15',
            'categoria' => 'seguros',
            'metodoPago' => 'tarjeta',
        ]);

        $gastoId = $createResponse['data']['id'];

        // Then update it
        $response = $this->put('/api/gastos/' . $gastoId, [
            'localId' => $this->localId,
            'concepto' => 'Updated Concepto',
            'importe' => 120.0,
            'fecha' => '2024-03-20',
            'categoria' => 'impuestos',
            'descripcion' => 'Updated description',
            'metodoPago' => 'transferencia',
        ]);

        $this->assertResponseStatusCode(200, $response);
        $this->assertEquals('Updated Concepto', $response['data']['concepto']);
        $this->assertEquals(120.0, $response['data']['importe']);
        $this->assertEquals('impuestos', $response['data']['categoria']);
    }

    public function testDeleteGasto(): void
    {
        // First create a gasto
        $createResponse = $this->post('/api/gastos', [
            'localId' => $this->localId,
            'concepto' => 'Delete Test',
            'importe' => 100.0,
            'fecha' => '2024-04-15',
            'categoria' => 'otros',
            'metodoPago' => 'efectivo',
        ]);

        $gastoId = $createResponse['data']['id'];

        // Then delete it
        $response = $this->delete('/api/gastos/' . $gastoId);

        $this->assertResponseStatusCode(204, $response);

        // Verify it's deleted
        $showResponse = $this->get('/api/gastos/' . $gastoId);
        $this->assertResponseStatusCode(404, $showResponse);
    }

    public function testListGastosByLocal(): void
    {
        // Create a gasto
        $this->post('/api/gastos', [
            'localId' => $this->localId,
            'concepto' => 'By Local Test',
            'importe' => 100.0,
            'fecha' => '2024-05-15',
            'categoria' => 'otros',
            'metodoPago' => 'transferencia',
        ]);

        $response = $this->get('/api/gastos/local/' . $this->localId);

        $this->assertResponseStatusCode(200, $response);
        $this->assertArrayHasKey('data', $response['data']);
        $this->assertGreaterThanOrEqual(1, count($response['data']['data']));
    }

    public function testListGastosWithCategoriaFilter(): void
    {
        // Create a gasto
        $this->post('/api/gastos', [
            'localId' => $this->localId,
            'concepto' => 'Filter Test',
            'importe' => 100.0,
            'fecha' => '2024-06-15',
            'categoria' => 'suministros',
            'metodoPago' => 'domiciliacion',
        ]);

        $response = $this->get('/api/gastos?categoria=suministros');

        $this->assertResponseStatusCode(200, $response);
        $this->assertArrayHasKey('data', $response['data']);
    }
}
