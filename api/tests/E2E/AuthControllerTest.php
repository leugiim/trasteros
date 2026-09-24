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

    public function testRefreshReturnsANewRefreshToken(): void
    {
        $login = $this->post('/api/auth/login', ['email' => 'admin@trasteros.test', 'password' => 'password123']);

        $response = $this->post('/api/auth/refresh', ['refreshToken' => $login['data']['refreshToken']]);

        $this->assertResponseStatusCode(200, $response);
        $this->assertNotEmpty($response['data']['token']);
        $this->assertNotSame($login['data']['refreshToken'], $response['data']['refreshToken']);
    }

    public function testRefreshTokenCanOnlyBeUsedOnce(): void
    {
        $login = $this->post('/api/auth/login', ['email' => 'admin@trasteros.test', 'password' => 'password123']);
        $this->assertResponseStatusCode(200, $this->post('/api/auth/refresh', ['refreshToken' => $login['data']['refreshToken']]));

        $response = $this->post('/api/auth/refresh', ['refreshToken' => $login['data']['refreshToken']]);

        $this->assertResponseStatusCode(401, $response);
        $this->assertHasError($response, 'INVALID_REFRESH_TOKEN');
    }

    public function testLoggingInAgainKeepsOtherSessions(): void
    {
        $first = $this->post('/api/auth/login', ['email' => 'admin@trasteros.test', 'password' => 'password123']);
        $this->post('/api/auth/login', ['email' => 'admin@trasteros.test', 'password' => 'password123']);

        // The first session (e.g. another device) can still refresh
        $response = $this->post('/api/auth/refresh', ['refreshToken' => $first['data']['refreshToken']]);

        $this->assertResponseStatusCode(200, $response);
    }

    public function testRefreshTokensAreStoredHashed(): void
    {
        $login = $this->post('/api/auth/login', ['email' => 'admin@trasteros.test', 'password' => 'password123']);
        $plain = $login['data']['refreshToken'];

        $stored = static::getContainer()->get(\Doctrine\DBAL\Connection::class)
            ->fetchFirstColumn('SELECT token FROM refresh_tokens');

        $this->assertNotContains($plain, $stored);
        $this->assertContains(hash('sha256', $plain), $stored);
    }

    public function testUnknownRefreshToken(): void
    {
        $response = $this->post('/api/auth/refresh', ['refreshToken' => 'not-a-real-token']);

        $this->assertResponseStatusCode(401, $response);
        $this->assertHasError($response, 'INVALID_REFRESH_TOKEN');
    }
}
