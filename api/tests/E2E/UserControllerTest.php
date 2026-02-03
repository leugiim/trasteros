<?php

declare(strict_types=1);

namespace App\Tests\E2E;

class UserControllerTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->authenticate();
    }

    public function testMeEndpointWithoutAuth(): void
    {
        $response = $this->get('/api/users/me');

        $this->assertResponseStatusCode(401, $response);
    }

    public function testMeEndpointWithAuth(): void
    {
        $this->authenticate();

        $response = $this->get('/api/users/me');

        $this->assertResponseStatusCode(200, $response);
        $this->assertEquals('admin@trasteros.test', $response['data']['email']);
        $this->assertArrayHasKey('activo', $response['data']);
        $this->assertArrayHasKey('createdAt', $response['data']);
        $this->assertArrayHasKey('updatedAt', $response['data']);
    }

    public function testListUsers(): void
    {
        $response = $this->get('/api/users');

        $this->assertResponseStatusCode(200, $response);
        $this->assertArrayHasKey('data', $response['data']);
        $this->assertArrayHasKey('meta', $response['data']);
        $this->assertGreaterThanOrEqual(1, count($response['data']['data']));
    }

    public function testListUsersWithoutAuth(): void
    {
        $this->authToken = null;
        $response = $this->get('/api/users');

        $this->assertResponseStatusCode(401, $response);
    }

    public function testCreateUser(): void
    {
        $response = $this->post('/api/users', [
            'nombre' => 'Test User',
            'email' => 'testuser@trasteros.test',
            'password' => 'testpassword123',
            'rol' => 'gestor',
            'activo' => true,
        ]);

        $this->assertResponseStatusCode(201, $response);
        $this->assertEquals('Test User', $response['data']['nombre']);
        $this->assertEquals('testuser@trasteros.test', $response['data']['email']);
        $this->assertEquals('gestor', $response['data']['rol']);
    }

    public function testCreateUserWithDuplicateEmail(): void
    {
        $response = $this->post('/api/users', [
            'nombre' => 'Duplicate User',
            'email' => 'admin@trasteros.test',
            'password' => 'password123',
            'rol' => 'gestor',
            'activo' => true,
        ]);

        $this->assertResponseStatusCode(400, $response);
        $this->assertHasError($response, 'VALIDATION_ERROR');
    }

    public function testShowUser(): void
    {
        // First create a user
        $createResponse = $this->post('/api/users', [
            'nombre' => 'Show Test User',
            'email' => 'showtest@trasteros.test',
            'password' => 'password123',
            'rol' => 'readonly',
            'activo' => true,
        ]);

        $userId = $createResponse['data']['id'];

        // Then fetch it
        $response = $this->get('/api/users/' . $userId);

        $this->assertResponseStatusCode(200, $response);
        $this->assertEquals('Show Test User', $response['data']['nombre']);
    }

    public function testShowNonExistentUser(): void
    {
        $response = $this->get('/api/users/99999999-9999-9999-9999-999999999999');

        $this->assertResponseStatusCode(404, $response);
        $this->assertHasError($response, 'USER_NOT_FOUND');
    }

    public function testUpdateUser(): void
    {
        // First create a user
        $createResponse = $this->post('/api/users', [
            'nombre' => 'Update Test User',
            'email' => 'updatetest@trasteros.test',
            'password' => 'password123',
            'rol' => 'readonly',
            'activo' => true,
        ]);

        $userId = $createResponse['data']['id'];

        // Then update it
        $response = $this->put('/api/users/' . $userId, [
            'nombre' => 'Updated User Name',
            'email' => 'updatetest@trasteros.test',
            'rol' => 'gestor',
            'activo' => false,
        ]);

        $this->assertResponseStatusCode(200, $response);
        $this->assertEquals('Updated User Name', $response['data']['nombre']);
        $this->assertEquals('gestor', $response['data']['rol']);
        $this->assertFalse($response['data']['activo']);
    }

    public function testDeleteUser(): void
    {
        // First create a user
        $createResponse = $this->post('/api/users', [
            'nombre' => 'Delete Test User',
            'email' => 'deletetest@trasteros.test',
            'password' => 'password123',
            'rol' => 'readonly',
            'activo' => true,
        ]);

        $userId = $createResponse['data']['id'];

        // Then delete it
        $response = $this->delete('/api/users/' . $userId);

        $this->assertResponseStatusCode(204, $response);

        // Verify it's deleted
        $showResponse = $this->get('/api/users/' . $userId);
        $this->assertResponseStatusCode(404, $showResponse);
    }
}
