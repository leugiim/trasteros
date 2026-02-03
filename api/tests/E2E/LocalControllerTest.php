<?php

declare(strict_types=1);

namespace App\Tests\E2E;

class LocalControllerTest extends ApiTestCase
{
    private int $direccionId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authenticate();
        $this->createDireccion();
    }

    private function createDireccion(): void
    {
        $response = $this->post('/api/direcciones', [
            'nombreVia' => 'Local Test Street',
            'codigoPostal' => '28010',
            'ciudad' => 'Madrid',
            'provincia' => 'Madrid',
            'pais' => 'Espana',
        ]);

        $this->direccionId = $response['data']['id'];
    }

    public function testListLocales(): void
    {
        $response = $this->get('/api/locales');

        $this->assertResponseStatusCode(200, $response);
        $this->assertArrayHasKey('data', $response['data']);
        $this->assertArrayHasKey('meta', $response['data']);
    }

    public function testListLocalesWithoutAuth(): void
    {
        $this->authToken = null;
        $response = $this->get('/api/locales');

        $this->assertResponseStatusCode(401, $response);
    }

    public function testCreateLocal(): void
    {
        $response = $this->post('/api/locales', [
            'nombre' => 'Nave Principal',
            'direccionId' => $this->direccionId,
            'superficieTotal' => 500.5,
            'numeroTrasteros' => 20,
            'fechaCompra' => '2020-01-15',
            'precioCompra' => 150000.0,
            'referenciaCatastral' => '1234567890123456789A',
            'valorCatastral' => 120000.0,
        ]);

        $this->assertResponseStatusCode(201, $response);
        $this->assertEquals('Nave Principal', $response['data']['nombre']);
        $this->assertEquals($this->direccionId, $response['data']['direccionId']);
        $this->assertEquals(500.5, $response['data']['superficieTotal']);
    }

    public function testCreateLocalWithNonExistentDireccion(): void
    {
        $response = $this->post('/api/locales', [
            'nombre' => 'Test Local',
            'direccionId' => 99999,
            'superficieTotal' => 100.0,
        ]);

        $this->assertResponseStatusCode(400, $response);
        $this->assertHasError($response, 'VALIDATION_ERROR');
    }

    public function testShowLocal(): void
    {
        // First create a local
        $createResponse = $this->post('/api/locales', [
            'nombre' => 'Show Test Local',
            'direccionId' => $this->direccionId,
            'superficieTotal' => 200.0,
        ]);

        $localId = $createResponse['data']['id'];

        // Then fetch it
        $response = $this->get('/api/locales/' . $localId);

        $this->assertResponseStatusCode(200, $response);
        $this->assertEquals('Show Test Local', $response['data']['nombre']);
    }

    public function testShowNonExistentLocal(): void
    {
        $response = $this->get('/api/locales/99999');

        $this->assertResponseStatusCode(404, $response);
        $this->assertHasError($response, 'LOCAL_NOT_FOUND');
    }

    public function testUpdateLocal(): void
    {
        // First create a local
        $createResponse = $this->post('/api/locales', [
            'nombre' => 'Update Test Local',
            'direccionId' => $this->direccionId,
            'superficieTotal' => 300.0,
        ]);

        $localId = $createResponse['data']['id'];

        // Then update it
        $response = $this->put('/api/locales/' . $localId, [
            'nombre' => 'Updated Local Name',
            'direccionId' => $this->direccionId,
            'superficieTotal' => 350.0,
            'numeroTrasteros' => 15,
        ]);

        $this->assertResponseStatusCode(200, $response);
        $this->assertEquals('Updated Local Name', $response['data']['nombre']);
        $this->assertEquals(350.0, $response['data']['superficieTotal']);
    }

    public function testDeleteLocal(): void
    {
        // First create a local
        $createResponse = $this->post('/api/locales', [
            'nombre' => 'Delete Test Local',
            'direccionId' => $this->direccionId,
            'superficieTotal' => 100.0,
        ]);

        $localId = $createResponse['data']['id'];

        // Then delete it
        $response = $this->delete('/api/locales/' . $localId);

        $this->assertResponseStatusCode(204, $response);

        // Verify it's deleted
        $showResponse = $this->get('/api/locales/' . $localId);
        $this->assertResponseStatusCode(404, $showResponse);
    }

    public function testListLocalesWithFilters(): void
    {
        // Create a local
        $this->post('/api/locales', [
            'nombre' => 'Filterable Local',
            'direccionId' => $this->direccionId,
            'superficieTotal' => 250.0,
        ]);

        // Filter by nombre
        $response = $this->get('/api/locales?nombre=Filterable');

        $this->assertResponseStatusCode(200, $response);
        $this->assertGreaterThanOrEqual(1, count($response['data']['data']));
    }
}
