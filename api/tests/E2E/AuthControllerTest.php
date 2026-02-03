<?php

declare(strict_types=1);

namespace App\Tests\E2E;

class AuthControllerTest extends ApiTestCase
{
    public function testLoginSuccess(): void
    {
        $response = $this->post('/api/auth/login', [
            'email' => 'admin@trasteros.test',
            'password' => 'password123',
        ]);

        $this->assertResponseStatusCode(200, $response);
        $this->assertArrayHasKey('token', $response['data']);
        $this->assertArrayHasKey('user', $response['data']);
        $this->assertEquals('admin@trasteros.test', $response['data']['user']['email']);
    }

    public function testLoginWithInvalidCredentials(): void
    {
        $response = $this->post('/api/auth/login', [
            'email' => 'admin@trasteros.test',
            'password' => 'wrongpassword',
        ]);

        $this->assertResponseStatusCode(401, $response);
        $this->assertHasError($response, 'INVALID_CREDENTIALS');
    }

    public function testLoginWithNonExistentUser(): void
    {
        $response = $this->post('/api/auth/login', [
            'email' => 'nonexistent@trasteros.test',
            'password' => 'password123',
        ]);

        $this->assertResponseStatusCode(401, $response);
        $this->assertHasError($response, 'INVALID_CREDENTIALS');
    }
}
