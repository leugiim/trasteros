<?php

declare(strict_types=1);

namespace App\Tests\E2E;

/**
 * The firewall protects everything under /api by default (security.yaml),
 * independently of #[Auth] on each controller.
 */
class SecurityTest extends ApiTestCase
{
    public function testProtectedRouteWithoutTokenReturnsApiError(): void
    {
        $response = $this->get('/api/clientes');

        $this->assertResponseStatusCode(401, $response);
        $this->assertHasError($response, 'UNAUTHORIZED');
    }

    public function testInvalidTokenReturnsApiError(): void
    {
        $response = $this->get('/api/clientes', ['HTTP_AUTHORIZATION' => 'Bearer not.a.jwt']);

        // Used to be lexik's own format ({"code": 401, "message": ...})
        $this->assertResponseStatusCode(401, $response);
        $this->assertHasError($response, 'UNAUTHORIZED');
    }

    public function testPublicRoutesDontNeedAToken(): void
    {
        $this->assertResponseStatusCode(200, $this->get('/api/health'));

        $response = $this->post('/api/auth/login', ['email' => 'admin@trasteros.test', 'password' => 'password123']);
        $this->assertResponseStatusCode(200, $response);
    }

    public function testDeactivatedUserTokenStopsWorking(): void
    {
        $this->authenticate();
        $created = $this->post('/api/users', [
            'nombre' => 'Temporal',
            'email' => 'temporal@trasteros.test',
            'password' => 'password123',
            'rol' => 'gestor',
            'activo' => true,
        ]);
        $this->assertResponseStatusCode(201, $created);
        $userId = $created['data']['id'];

        $login = $this->post('/api/auth/login', ['email' => 'temporal@trasteros.test', 'password' => 'password123']);
        $userToken = $login['data']['token'];

        $update = $this->put("/api/users/{$userId}", [
            'nombre' => 'Temporal',
            'email' => 'temporal@trasteros.test',
            'rol' => 'gestor',
            'activo' => false,
        ]);
        $this->assertResponseStatusCode(200, $update);

        $response = $this->get('/api/clientes', ['HTTP_AUTHORIZATION' => 'Bearer ' . $userToken]);

        $this->assertResponseStatusCode(401, $response);
        $this->assertHasError($response, 'UNAUTHORIZED');
    }
}
