<?php

declare(strict_types=1);

namespace App\Tests\E2E;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

abstract class ApiTestCase extends WebTestCase
{
    protected KernelBrowser $client;
    protected ?string $authToken = null;
    private static bool $databaseInitialized = false;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // Reset database for each test class
        self::resetDatabase();
    }

    private static function resetDatabase(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $projectDir = $kernel->getProjectDir();
        $dbPath = $projectDir . '/var/data_tests_e2e.db';

        // Remove existing test database
        if (file_exists($dbPath)) {
            unlink($dbPath);
        }

        // Run migrations
        $application->run(
            new ArrayInput([
                'command' => 'doctrine:migrations:migrate',
                '--no-interaction' => true,
                '--env' => 'test',
            ]),
            new NullOutput()
        );

        // Run database seeds
        $application->run(
            new ArrayInput([
                'command' => 'app:database:seeds',
                '--no-interaction' => true,
                '--env' => 'test',
            ]),
            new NullOutput()
        );

        self::ensureKernelShutdown();
        self::$databaseInitialized = true;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
    }

    protected function authenticate(string $email = 'admin@trasteros.test', string $password = 'password123'): void
    {
        $this->client->request(
            'POST',
            '/api/auth/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => $email,
                'password' => $password,
            ])
        );

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->authToken = $response['token'] ?? null;
    }

    protected function apiRequest(
        string $method,
        string $uri,
        array $data = [],
        array $headers = []
    ): array {
        $defaultHeaders = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ];

        if ($this->authToken) {
            $defaultHeaders['HTTP_AUTHORIZATION'] = 'Bearer ' . $this->authToken;
        }

        $headers = array_merge($defaultHeaders, $headers);

        $content = !empty($data) ? json_encode($data) : null;

        $this->client->request($method, $uri, [], [], $headers, $content);

        $response = $this->client->getResponse();
        $content = $response->getContent();

        return [
            'status' => $response->getStatusCode(),
            'data' => $content ? json_decode($content, true) : null,
        ];
    }

    protected function get(string $uri, array $headers = []): array
    {
        return $this->apiRequest('GET', $uri, [], $headers);
    }

    protected function post(string $uri, array $data = [], array $headers = []): array
    {
        return $this->apiRequest('POST', $uri, $data, $headers);
    }

    protected function put(string $uri, array $data = [], array $headers = []): array
    {
        return $this->apiRequest('PUT', $uri, $data, $headers);
    }

    protected function delete(string $uri, array $headers = []): array
    {
        return $this->apiRequest('DELETE', $uri, [], $headers);
    }

    protected function assertResponseStatusCode(int $expected, array $response): void
    {
        $this->assertEquals(
            $expected,
            $response['status'],
            sprintf(
                'Expected status code %d, got %d. Response: %s',
                $expected,
                $response['status'],
                json_encode($response['data'])
            )
        );
    }

    protected function assertHasError(array $response, string $code): void
    {
        $this->assertArrayHasKey('error', $response['data']);
        $this->assertEquals($code, $response['data']['error']['code']);
    }

    /**
     * Generate a valid Spanish DNI for testing
     */
    protected function generateValidDni(int $seed = 0): string
    {
        $letters = 'TRWAGMYFPDXBNJZSQVHLCKE';
        // Use different base numbers for different tests
        $number = 12345670 + $seed;
        $letter = $letters[$number % 23];
        return sprintf('%08d%s', $number, $letter);
    }

    /**
     * Generate a valid Spanish NIE for testing
     */
    protected function generateValidNie(int $seed = 0): string
    {
        $letters = 'TRWAGMYFPDXBNJZSQVHLCKE';
        $prefixes = ['X', 'Y', 'Z'];
        $prefix = $prefixes[$seed % 3];
        $prefixValue = array_search($prefix, $prefixes);
        $number = 1234567 + $seed;
        $fullNumber = intval($prefixValue . sprintf('%07d', $number));
        $letter = $letters[$fullNumber % 23];
        return $prefix . sprintf('%07d', $number) . $letter;
    }
}
