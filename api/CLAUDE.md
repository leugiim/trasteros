# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Symfony 7.4 API project using API Platform for REST API development with JWT authentication.

## Key Commands

```bash
# Start development server
symfony server:start
# or
php -S localhost:8000 -t public/

# Clear cache
php bin/console cache:clear

# Database operations
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:migrations:diff    # Generate migration from entity changes
php bin/console app:database:seeds          # Seed database with test data
# or
php scripts/seeds.php                       # Alternative: run seeds script directly
php scripts/seeds.php --env=test            # Seed test database

# Debug routes
php bin/console debug:router

# Generate JWT keypair (if needed)
php bin/console lexik:jwt:generate-keypair
```

## Architecture

- **API Platform 4.x** with Doctrine ORM for automatic CRUD endpoints
- **JWT Authentication** via LexikJWTAuthenticationBundle (keys in `config/jwt/`)
- **CORS** handled by NelmioCorsBundle
- **Database**: SQLite by default (configurable via `DATABASE_URL` in `.env`)

## Directory Structure

- `src/Entity/` - Doctrine entities with API Platform attributes
- `config/packages/` - Bundle configurations
- `config/jwt/` - JWT private/public keys (not committed)

## Entity Creation Pattern

Entities use PHP 8 attributes for both Doctrine and API Platform:

```php
use ApiPlatform\Metadata\ApiResource;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ApiResource]
class MyEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
}
```

## Database Seeds

The project includes a comprehensive seeding system for test data:

**Command**: `php bin/console app:database:seeds`
- Location: `src/Shared/Infrastructure/CLI/DatabaseSeedsCommand.php`
- Creates complete test dataset: users, direcciones, locales, clientes, trasteros, contratos, gastos, ingresos, prestamos
- Used automatically by E2E tests in `ApiTestCase::resetDatabase()`
- Can be run manually for development environment

**Script**: `php scripts/seeds.php [--env=dev|test]`
- Wrapper script that calls the Symfony command
- Supports environment selection via `--env` parameter
- Default environment: `dev`

**Test Data Created**:
- 2 users (admin@trasteros.test, gestor@trasteros.test) - password: password123
- 2 direcciones (Madrid, Barcelona)
- 2 locales with associated data
- 2 clientes, 2 trasteros, 2 contratos
- Sample gastos, ingresos, and prestamos

## Testing

E2E tests extend `App\Tests\E2E\ApiTestCase` which:
- Automatically resets SQLite test database before each test class
- Runs migrations
- Seeds database with complete test data
- Provides helper methods: `authenticate()`, `get()`, `post()`, `put()`, `delete()`
- Test database location: `var/data_tests_e2e.db`

## Environment Variables

Key variables in `.env`:
- `DATABASE_URL` - Database connection string
- `JWT_SECRET_KEY` / `JWT_PUBLIC_KEY` - Paths to JWT keys
- `JWT_PASSPHRASE` - Passphrase for JWT private key
- `CORS_ALLOW_ORIGIN` - Allowed CORS origins regex
