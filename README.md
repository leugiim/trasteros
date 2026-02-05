# Trasteros

Sistema de gestión de trasteros (storage unit management system).

## Estructura del Proyecto

| Directorio | Tecnología | Descripción |
|------------|------------|-------------|
| `/api` | Symfony 7.4 | API REST con autenticación JWT |
| `/app` | Next.js 16 | Aplicación frontend |

## Requisitos

- PHP 8.2+
- Composer
- Node.js 20+
- pnpm

## Inicio Rápido

### API

```bash
cd api
composer install
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console lexik:jwt:generate-keypair
php bin/console app:database:seeds  # Datos de prueba
symfony server:start
```

### Frontend

```bash
cd app
pnpm install
pnpm dev
```

## Documentación

- [API CLAUDE.md](api/CLAUDE.md) - Documentación técnica del backend
- [App CLAUDE.md](app/CLAUDE.md) - Documentación técnica del frontend
- [API Reference](api/openapi.json) - Especificación OpenAPI
