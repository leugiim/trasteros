<?php

declare(strict_types=1);

namespace App\Tests\E2E;

class DireccionControllerTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->authenticate();
    }

    public function testListDirecciones(): void
    {
        $response = $this->get('/api/direcciones');

        $this->assertResponseStatusCode(200, $response);
        $this->assertArrayHasKey('data', $response['data']);
        $this->assertArrayHasKey('meta', $response['data']);
    }

    public function testListDireccionesWithoutAuth(): void
    {
        $this->authToken = null;
        $response = $this->get('/api/direcciones');

        $this->assertResponseStatusCode(401, $response);
    }

    public function testCreateDireccion(): void
    {
        $response = $this->post('/api/direcciones', [
            'tipoVia' => 'Calle',
            'nombreVia' => 'Gran Via',
            'numero' => '123',
            'piso' => '2',
            'puerta' => 'A',
            'codigoPostal' => '28001',
            'ciudad' => 'Madrid',
            'provincia' => 'Madrid',
            'pais' => 'Espana',
            'latitud' => 40.4168,
            'longitud' => -3.7038,
        ]);

        $this->assertResponseStatusCode(201, $response);
        $this->assertEquals('Gran Via', $response['data']['nombreVia']);
        $this->assertEquals('28001', $response['data']['codigoPostal']);
        $this->assertEquals('Madrid', $response['data']['ciudad']);
    }

    public function testCreateDireccionWithInvalidCodigoPostal(): void
    {
        // Note: Currently the API doesn't validate codigo postal format
        // This test documents the current behavior, not the desired behavior
        $response = $this->post('/api/direcciones', [
            'nombreVia' => 'Test Street',
            'codigoPostal' => 'INVALID',
            'ciudad' => 'Madrid',
            'provincia' => 'Madrid',
            'pais' => 'Espana',
        ]);

        // Currently accepts invalid codigo postal (should be 400 in the future)
        $this->assertResponseStatusCode(201, $response);
        $this->assertEquals('INVALID', $response['data']['codigoPostal']);
    }

    public function testShowDireccion(): void
    {
        // First create a direccion
        $createResponse = $this->post('/api/direcciones', [
            'nombreVia' => 'Show Test Street',
            'codigoPostal' => '28002',
            'ciudad' => 'Madrid',
            'provincia' => 'Madrid',
            'pais' => 'Espana',
        ]);

        $direccionId = $createResponse['data']['id'];

        // Then fetch it
        $response = $this->get('/api/direcciones/' . $direccionId);

        $this->assertResponseStatusCode(200, $response);
        $this->assertEquals('Show Test Street', $response['data']['nombreVia']);
    }

    public function testShowNonExistentDireccion(): void
    {
        $response = $this->get('/api/direcciones/99999');

        $this->assertResponseStatusCode(404, $response);
        $this->assertHasError($response, 'DIRECCION_NOT_FOUND');
    }

    public function testUpdateDireccion(): void
    {
        // First create a direccion
        $createResponse = $this->post('/api/direcciones', [
            'nombreVia' => 'Update Test Street',
            'codigoPostal' => '28003',
            'ciudad' => 'Madrid',
            'provincia' => 'Madrid',
            'pais' => 'Espana',
        ]);

        $direccionId = $createResponse['data']['id'];

        // Then update it
        $response = $this->put('/api/direcciones/' . $direccionId, [
            'nombreVia' => 'Updated Street Name',
            'codigoPostal' => '28004',
            'ciudad' => 'Barcelona',
            'provincia' => 'Barcelona',
            'pais' => 'Espana',
        ]);

        $this->assertResponseStatusCode(200, $response);
        $this->assertEquals('Updated Street Name', $response['data']['nombreVia']);
        $this->assertEquals('Barcelona', $response['data']['ciudad']);
    }

    public function testDeleteDireccion(): void
    {
        // First create a direccion
        $createResponse = $this->post('/api/direcciones', [
            'nombreVia' => 'Delete Test Street',
            'codigoPostal' => '28005',
            'ciudad' => 'Madrid',
            'provincia' => 'Madrid',
            'pais' => 'Espana',
        ]);

        $direccionId = $createResponse['data']['id'];

        // Then delete it
        $response = $this->delete('/api/direcciones/' . $direccionId);

        $this->assertResponseStatusCode(204, $response);

        // Verify it's deleted
        $showResponse = $this->get('/api/direcciones/' . $direccionId);
        $this->assertResponseStatusCode(404, $showResponse);
    }

    public function testListDireccionesWithFilters(): void
    {
        // Create a direccion
        $this->post('/api/direcciones', [
            'nombreVia' => 'Filter Street',
            'codigoPostal' => '08001',
            'ciudad' => 'Barcelona',
            'provincia' => 'Barcelona',
            'pais' => 'Espana',
        ]);

        // Filter by ciudad
        $response = $this->get('/api/direcciones?ciudad=Barcelona');

        $this->assertResponseStatusCode(200, $response);
        $this->assertGreaterThanOrEqual(1, count($response['data']['data']));
    }
}
