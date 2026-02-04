<?php

declare(strict_types=1);

namespace App\Tests\E2E;

class PrestamoControllerTest extends ApiTestCase
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
            'nombreVia' => 'Prestamo Test Street',
            'codigoPostal' => '28060',
            'ciudad' => 'Madrid',
            'provincia' => 'Madrid',
            'pais' => 'Espana',
        ]);

        // Create local
        $localResponse = $this->post('/api/locales', [
            'nombre' => 'Prestamo Test Local',
            'direccionId' => $direccionResponse['data']['id'],
            'superficieTotal' => 500.0,
            'precioCompra' => 200000.0,
        ]);

        $this->localId = $localResponse['data']['id'];
    }

    public function testListPrestamos(): void
    {
        $response = $this->get('/api/prestamos');

        $this->assertResponseStatusCode(200, $response);
        $this->assertArrayHasKey('data', $response['data']);
        $this->assertArrayHasKey('meta', $response['data']);
    }

    public function testListPrestamosWithoutAuth(): void
    {
        $this->authToken = null;
        $response = $this->get('/api/prestamos');

        $this->assertResponseStatusCode(401, $response);
    }

    public function testCreatePrestamo(): void
    {
        $response = $this->post('/api/prestamos', [
            'localId' => $this->localId,
            'capitalSolicitado' => 100000.0,
            'totalADevolver' => 120000.0,
            'fechaConcesion' => '2024-01-01',
            'entidadBancaria' => 'Banco Test',
            'numeroPrestamo' => 'PREST-2024-001',
            'tipoInteres' => 3.5,
        ]);

        $this->assertResponseStatusCode(201, $response);
        $this->assertEquals(100000.0, $response['data']['capitalSolicitado']);
        $this->assertEquals('Banco Test', $response['data']['entidadBancaria']);
        $this->assertEquals('activo', $response['data']['estado']);
    }

    public function testCreatePrestamoWithNonExistentLocal(): void
    {
        $response = $this->post('/api/prestamos', [
            'localId' => 99999,
            'capitalSolicitado' => 100000.0,
            'totalADevolver' => 120000.0,
            'fechaConcesion' => '2024-01-01',
            'entidadBancaria' => 'Banco Test',
            'tipoInteres' => 3.5,
        ]);

        $this->assertResponseStatusCode(404, $response);
        $this->assertHasError($response, 'LOCAL_NOT_FOUND');
    }

    public function testShowPrestamo(): void
    {
        // First create a prestamo
        $createResponse = $this->post('/api/prestamos', [
            'localId' => $this->localId,
            'capitalSolicitado' => 150000.0,
            'totalADevolver' => 180000.0,
            'fechaConcesion' => '2024-02-01',
            'entidadBancaria' => 'Banco Show Test',
            'tipoInteres' => 4.0,
        ]);

        $prestamoId = $createResponse['data']['id'];

        // Then fetch it
        $response = $this->get('/api/prestamos/' . $prestamoId);

        $this->assertResponseStatusCode(200, $response);
        $this->assertEquals(150000.0, $response['data']['capitalSolicitado']);
    }

    public function testShowNonExistentPrestamo(): void
    {
        $response = $this->get('/api/prestamos/99999');

        $this->assertResponseStatusCode(404, $response);
        $this->assertHasError($response, 'PRESTAMO_NOT_FOUND');
    }

    public function testUpdatePrestamo(): void
    {
        // First create a prestamo
        $createResponse = $this->post('/api/prestamos', [
            'localId' => $this->localId,
            'capitalSolicitado' => 80000.0,
            'totalADevolver' => 95000.0,
            'fechaConcesion' => '2024-03-01',
            'entidadBancaria' => 'Banco Update Test',
            'tipoInteres' => 3.0,
        ]);

        $prestamoId = $createResponse['data']['id'];

        // Then update it
        $response = $this->put('/api/prestamos/' . $prestamoId, [
            'localId' => $this->localId,
            'capitalSolicitado' => 80000.0,
            'totalADevolver' => 96000.0,
            'fechaConcesion' => '2024-03-01',
            'entidadBancaria' => 'Updated Bank Name',
            'numeroPrestamo' => 'PREST-2024-002',
            'tipoInteres' => 3.2,
        ]);

        $this->assertResponseStatusCode(200, $response);
        $this->assertEquals('Updated Bank Name', $response['data']['entidadBancaria']);
        $this->assertEquals(3.2, $response['data']['tipoInteres']);
    }

    public function testDeletePrestamo(): void
    {
        // First create a prestamo
        $createResponse = $this->post('/api/prestamos', [
            'localId' => $this->localId,
            'capitalSolicitado' => 50000.0,
            'totalADevolver' => 60000.0,
            'fechaConcesion' => '2024-04-01',
            'entidadBancaria' => 'Banco Delete Test',
            'tipoInteres' => 2.5,
        ]);

        $prestamoId = $createResponse['data']['id'];

        // Then delete it
        $response = $this->delete('/api/prestamos/' . $prestamoId);

        $this->assertResponseStatusCode(204, $response);

        // Verify it's deleted
        $showResponse = $this->get('/api/prestamos/' . $prestamoId);
        $this->assertResponseStatusCode(404, $showResponse);
    }

    public function testListPrestamosByLocal(): void
    {
        // Create a prestamo
        $this->post('/api/prestamos', [
            'localId' => $this->localId,
            'capitalSolicitado' => 70000.0,
            'totalADevolver' => 84000.0,
            'fechaConcesion' => '2024-05-01',
            'entidadBancaria' => 'Banco By Local Test',
            'tipoInteres' => 3.0,
        ]);

        $response = $this->get('/api/prestamos/local/' . $this->localId);

        $this->assertResponseStatusCode(200, $response);
        $this->assertArrayHasKey('data', $response['data']);
        $this->assertGreaterThanOrEqual(1, count($response['data']['data']));
    }

    public function testListPrestamosWithEstadoFilter(): void
    {
        // Create a prestamo
        $this->post('/api/prestamos', [
            'localId' => $this->localId,
            'capitalSolicitado' => 60000.0,
            'totalADevolver' => 72000.0,
            'fechaConcesion' => '2024-06-01',
            'entidadBancaria' => 'Banco Filter Test',
            'tipoInteres' => 3.0,
        ]);

        $response = $this->get('/api/prestamos?estado=activo');

        $this->assertResponseStatusCode(200, $response);
        $this->assertArrayHasKey('data', $response['data']);
    }
}
