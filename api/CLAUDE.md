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

## Environment Variables

Key variables in `.env`:
- `DATABASE_URL` - Database connection string
- `JWT_SECRET_KEY` / `JWT_PUBLIC_KEY` - Paths to JWT keys
- `JWT_PASSPHRASE` - Passphrase for JWT private key
- `CORS_ALLOW_ORIGIN` - Allowed CORS origins regex
