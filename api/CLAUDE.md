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

## API Reference

Full OpenAPI 3.0 specification available at `openapi.json` (regenerate with `php bin/console nelmio:apidoc:dump --format=json > openapi.json`).

### Endpoints Summary

| Resource | Endpoints |
|----------|-----------|
| Auth | `POST /api/auth/login` |
| Users | `GET/POST /api/users`, `GET/PUT/DELETE /api/users/{id}` |
| Clientes | `GET/POST /api/clientes`, `GET/PUT/DELETE /api/clientes/{id}` |
| Locales | `GET/POST /api/locales`, `GET/PUT/DELETE /api/locales/{id}` |
| Trasteros | `GET/POST /api/trasteros`, `GET/PUT/DELETE /api/trasteros/{id}` |
| Contratos | `GET/POST /api/contratos`, `GET/PUT/DELETE /api/contratos/{id}`, `PATCH .../finalizar`, `PATCH .../cancelar` |
| Ingresos | `GET/POST /api/ingresos`, `GET/PUT/DELETE /api/ingresos/{id}` |
| Gastos | `GET/POST /api/gastos`, `GET/PUT/DELETE /api/gastos/{id}` |
| Prestamos | `GET/POST /api/prestamos`, `GET/PUT/DELETE /api/prestamos/{id}` |
| Direcciones | `GET/POST /api/direcciones`, `GET/PUT/DELETE /api/direcciones/{id}` |
| Dashboard | `GET /api/dashboard/stats`, `GET /api/dashboard/rentabilidad` |

### Error Codes

| HTTP | Code Pattern | Description |
|------|--------------|-------------|
| 400 | `VALIDATION_ERROR` | Invalid field value. Details object contains field names with error messages |
| 401 | `INVALID_CREDENTIALS` | Wrong email/password |
| 403 | `USER_INACTIVE` | User account is disabled |
| 404 | `{ENTITY}_NOT_FOUND` | Resource not found (e.g., `CLIENTE_NOT_FOUND`, `TRASTERO_NOT_FOUND`) |
| 409 | `{ENTITY}_ALREADY_EXISTS` or `VALIDATION_ERROR` | Duplicate resource or field conflict |

### Main Entities

- **User**: id (uuid), nombre, email, rol (admin/gestor/readonly), activo
- **Cliente**: id, nombre, apellidos, dniNie, email, telefono, activo
- **Local**: id, nombre, direccionId, superficieTotal, numeroTrasteros, fechaCompra, precioCompra, referenciaCatastral
- **Trastero**: id, localId, numero, nombre, superficie, precioMensual, estado (disponible/ocupado/reservado/mantenimiento)
- **Contrato**: id, trasteroId, clienteId, fechaInicio, fechaFin, precioMensual, fianza, fianzaPagada, estado (activo/finalizado/cancelado)
- **Ingreso**: id, contratoId, concepto, importe, fechaPago, categoria (alquiler/fianza/otros), metodoPago
- **Gasto**: id, localId, concepto, importe, fecha, categoria (suministros/mantenimiento/seguros/impuestos/comunidad/otros), metodoPago
- **Prestamo**: id, localId, capitalSolicitado, totalADevolver, fechaConcesion, entidadBancaria, tipoInteres, estado (activo/pagado/cancelado)
- **Direccion**: id, tipoVia, nombreVia, numero, piso, puerta, codigoPostal, ciudad, provincia, pais, latitud, longitud
