<?php

declare(strict_types=1);

namespace App\Tests\E2E;

class TrasteroControllerTest extends ApiTestCase
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
            'nombreVia' => 'Trastero Test Street',
            'codigoPostal' => '28020',
            'ciudad' => 'Madrid',
            'provincia' => 'Madrid',
            'pais' => 'Espana',
        ]);

        // Create local
        $localResponse = $this->post('/api/locales', [
            'nombre' => 'Trastero Test Local',
            'direccionId' => $direccionResponse['data']['id'],
            'superficieTotal' => 500.0,
        ]);

        $this->localId = $localResponse['data']['id'];
    }

    public function testListTrasteros(): void
    {
        $response = $this->get('/api/trasteros');

        $this->assertResponseStatusCode(200, $response);
        $this->assertArrayHasKey('data', $response['data']);
        $this->assertArrayHasKey('meta', $response['data']);
    }

    public function testListTrasterosWithoutAuth(): void
    {
        $this->authToken = null;
        $response = $this->get('/api/trasteros');

        $this->assertResponseStatusCode(401, $response);
    }

    public function testCreateTrastero(): void
    {
        $response = $this->post('/api/trasteros', [
            'localId' => $this->localId,
            'numero' => 'A-01',
            'nombre' => 'Trastero Pequeno',
            'superficie' => 5.5,
            'precioMensual' => 50.0,
            'estado' => 'disponible',
        ]);

        $this->assertResponseStatusCode(201, $response);
        $this->assertEquals('A-01', $response['data']['numero']);
        $this->assertEquals($this->localId, $response['data']['localId']);
        $this->assertEquals(5.5, $response['data']['superficie']);
        $this->assertEquals('disponible', $response['data']['estado']);
    }

    public function testCreateTrasteroWithNonExistentLocal(): void
    {
        $response = $this->post('/api/trasteros', [
            'localId' => 99999,
            'numero' => 'X-01',
            'superficie' => 5.0,
            'precioMensual' => 50.0,
            'estado' => 'disponible',
        ]);

        $this->assertResponseStatusCode(404, $response);
        $this->assertHasError($response, 'LOCAL_NOT_FOUND');
    }

    public function testShowTrastero(): void
    {
        // First create a trastero
        $createResponse = $this->post('/api/trasteros', [
            'localId' => $this->localId,
            'numero' => 'B-01',
            'superficie' => 10.0,
            'precioMensual' => 100.0,
            'estado' => 'disponible',
        ]);

        $trasteroId = $createResponse['data']['id'];

        // Then fetch it
        $response = $this->get('/api/trasteros/' . $trasteroId);

        $this->assertResponseStatusCode(200, $response);
        $this->assertEquals('B-01', $response['data']['numero']);
    }

    public function testShowNonExistentTrastero(): void
    {
        $response = $this->get('/api/trasteros/99999');

        $this->assertResponseStatusCode(404, $response);
        $this->assertHasError($response, 'TRASTERO_NOT_FOUND');
    }

    public function testUpdateTrastero(): void
    {
        // First create a trastero
        $createResponse = $this->post('/api/trasteros', [
            'localId' => $this->localId,
            'numero' => 'C-01',
            'superficie' => 8.0,
            'precioMensual' => 80.0,
            'estado' => 'disponible',
        ]);

        $trasteroId = $createResponse['data']['id'];

        // Then update it
        $response = $this->put('/api/trasteros/' . $trasteroId, [
            'localId' => $this->localId,
            'numero' => 'C-01-Updated',
            'nombre' => 'Trastero Actualizado',
            'superficie' => 10.0,
            'precioMensual' => 100.0,
            'estado' => 'mantenimiento',
        ]);

        $this->assertResponseStatusCode(200, $response);
        $this->assertEquals('C-01-Updated', $response['data']['numero']);
        $this->assertEquals('mantenimiento', $response['data']['estado']);
    }

    public function testDeleteTrastero(): void
    {
        // First create a trastero
        $createResponse = $this->post('/api/trasteros', [
            'localId' => $this->localId,
            'numero' => 'D-01',
            'superficie' => 6.0,
            'precioMensual' => 60.0,
            'estado' => 'disponible',
        ]);

        $trasteroId = $createResponse['data']['id'];

        // Then delete it
        $response = $this->delete('/api/trasteros/' . $trasteroId);

        $this->assertResponseStatusCode(204, $response);

        // Verify it's deleted
        $showResponse = $this->get('/api/trasteros/' . $trasteroId);
        $this->assertResponseStatusCode(404, $showResponse);
    }

    public function testListTrasterosByLocal(): void
    {
        // Create a trastero
        $this->post('/api/trasteros', [
            'localId' => $this->localId,
            'numero' => 'E-01',
            'superficie' => 5.0,
            'precioMensual' => 50.0,
            'estado' => 'disponible',
        ]);

        // Get trasteros by local
        $response = $this->get('/api/trasteros/local/' . $this->localId);

        $this->assertResponseStatusCode(200, $response);
        $this->assertArrayHasKey('data', $response['data']);
        $this->assertGreaterThanOrEqual(1, count($response['data']['data']));
    }

    public function testListTrasterosDisponibles(): void
    {
        // Create a disponible trastero
        $this->post('/api/trasteros', [
            'localId' => $this->localId,
            'numero' => 'F-01',
            'superficie' => 5.0,
            'precioMensual' => 50.0,
            'estado' => 'disponible',
        ]);

        // Get disponibles
        $response = $this->get('/api/trasteros/disponibles');

        $this->assertResponseStatusCode(200, $response);
        $this->assertArrayHasKey('data', $response['data']);
    }
}
