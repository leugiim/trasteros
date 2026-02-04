<?php

declare(strict_types=1);

namespace App\Tests\E2E;

class ClienteControllerTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->authenticate();
    }

    public function testListClientes(): void
    {
        $response = $this->get('/api/clientes');

        $this->assertResponseStatusCode(200, $response);
        $this->assertArrayHasKey('data', $response['data']);
        $this->assertArrayHasKey('meta', $response['data']);
    }

    public function testListClientesWithoutAuth(): void
    {
        $this->authToken = null;
        $response = $this->get('/api/clientes');

        $this->assertResponseStatusCode(401, $response);
    }

    public function testCreateCliente(): void
    {
        $dni = $this->generateValidDni(1);

        $response = $this->post('/api/clientes', [
            'nombre' => 'Juan',
            'apellidos' => 'Garcia Lopez',
            'dniNie' => $dni,
            'email' => 'juan@example.com',
            'telefono' => '+34612345678',
        ]);

        $this->assertResponseStatusCode(201, $response);
        $this->assertEquals('Juan', $response['data']['nombre']);
        $this->assertEquals('Garcia Lopez', $response['data']['apellidos']);
        $this->assertEquals($dni, $response['data']['dniNie']);
    }

    public function testCreateClienteWithInvalidDni(): void
    {
        $response = $this->post('/api/clientes', [
            'nombre' => 'Test',
            'apellidos' => 'Invalid DNI',
            'dniNie' => 'INVALID',
            'email' => 'invalid@example.com',
            'telefono' => '+34612345678',
        ]);

        $this->assertResponseStatusCode(400, $response);
        $this->assertHasError($response, 'VALIDATION_ERROR');
    }

    public function testCreateClienteWithDuplicateDni(): void
    {
        $dni = $this->generateValidDni(2);

        // First create a cliente
        $this->post('/api/clientes', [
            'nombre' => 'First',
            'apellidos' => 'Cliente',
            'dniNie' => $dni,
            'email' => 'first@example.com',
            'telefono' => '+34612345678',
        ]);

        // Try to create another with same DNI
        $response = $this->post('/api/clientes', [
            'nombre' => 'Second',
            'apellidos' => 'Cliente',
            'dniNie' => $dni,
            'email' => 'second@example.com',
            'telefono' => '+34612345678',
        ]);

        $this->assertResponseStatusCode(409, $response);
        $this->assertHasError($response, 'ALREADY_EXISTS');
    }

    public function testShowCliente(): void
    {
        $dni = $this->generateValidDni(3);

        // First create a cliente
        $createResponse = $this->post('/api/clientes', [
            'nombre' => 'Show',
            'apellidos' => 'Test Cliente',
            'dniNie' => $dni,
            'email' => 'showtest@example.com',
            'telefono' => '+34612345678',
        ]);

        $clienteId = $createResponse['data']['id'];

        // Then fetch it
        $response = $this->get('/api/clientes/' . $clienteId);

        $this->assertResponseStatusCode(200, $response);
        $this->assertEquals('Show', $response['data']['nombre']);
    }

    public function testShowNonExistentCliente(): void
    {
        $response = $this->get('/api/clientes/99999');

        $this->assertResponseStatusCode(404, $response);
        $this->assertHasError($response, 'CLIENTE_NOT_FOUND');
    }

    public function testUpdateCliente(): void
    {
        $dni = $this->generateValidDni(4);

        // First create a cliente
        $createResponse = $this->post('/api/clientes', [
            'nombre' => 'Update',
            'apellidos' => 'Test',
            'dniNie' => $dni,
            'email' => 'update@example.com',
            'telefono' => '+34612345678',
        ]);

        $clienteId = $createResponse['data']['id'];

        // Then update it
        $response = $this->put('/api/clientes/' . $clienteId, [
            'nombre' => 'Updated',
            'apellidos' => 'Cliente',
            'dniNie' => $dni,
            'email' => 'updated@example.com',
            'telefono' => '+34699999999',
        ]);

        $this->assertResponseStatusCode(200, $response);
        $this->assertEquals('Updated', $response['data']['nombre']);
        $this->assertEquals('updated@example.com', $response['data']['email']);
    }

    public function testDeleteCliente(): void
    {
        $dni = $this->generateValidDni(5);

        // First create a cliente
        $createResponse = $this->post('/api/clientes', [
            'nombre' => 'Delete',
            'apellidos' => 'Test',
            'dniNie' => $dni,
            'email' => 'delete@example.com',
            'telefono' => '+34612345678',
        ]);

        $clienteId = $createResponse['data']['id'];

        // Then delete it
        $response = $this->delete('/api/clientes/' . $clienteId);

        $this->assertResponseStatusCode(204, $response);

        // Verify it's deleted
        $showResponse = $this->get('/api/clientes/' . $clienteId);
        $this->assertResponseStatusCode(404, $showResponse);
    }

    public function testListClientesWithFilters(): void
    {
        $dni = $this->generateValidDni(6);

        // Create a cliente
        $this->post('/api/clientes', [
            'nombre' => 'FilterTest',
            'apellidos' => 'Test',
            'dniNie' => $dni,
            'email' => 'filter@example.com',
            'telefono' => '+34612345678',
        ]);

        // Filter by nombre
        $response = $this->get('/api/clientes?nombre=FilterTest');

        $this->assertResponseStatusCode(200, $response);
        $this->assertGreaterThanOrEqual(1, count($response['data']['data']));
    }
}
