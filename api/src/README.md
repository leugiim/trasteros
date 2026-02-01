# Sistema de Gestión de Trasteros - API

Sistema completo de gestión de trasteros implementado con Symfony 7.4 siguiendo arquitectura hexagonal y CQRS.

## Descripción General

Este proyecto gestiona el alquiler de trasteros distribuidos en diferentes locales. Incluye gestión de clientes, contratos de alquiler, ingresos, gastos, préstamos bancarios y direcciones.

## Arquitectura

El proyecto sigue **Arquitectura Hexagonal** (Ports & Adapters) con **CQRS** (Command Query Responsibility Segregation):

### Capas de la Arquitectura

```
src/[Modulo]/
├── Domain/              # Lógica de negocio pura
│   ├── Model/          # Entidades, Value Objects, Enums
│   ├── Repository/     # Interfaces de repositorios
│   ├── Event/          # Eventos de dominio
│   ├── Exception/      # Excepciones de dominio
│   └── Service/        # Servicios de dominio (opcional)
│
├── Application/         # Casos de uso
│   ├── Command/        # Comandos (escritura)
│   ├── Query/          # Queries (lectura)
│   └── DTO/            # Data Transfer Objects
│
└── Infrastructure/      # Implementaciones técnicas
    ├── Persistence/    # Repositorios Doctrine
    ├── Controller/     # Controladores API REST
    ├── CLI/            # Comandos de consola
    └── EventSubscriber/# Suscriptores de eventos
```

### Principios Aplicados

- **Domain-Driven Design (DDD)**: Modelado del dominio de negocio
- **SOLID**: Principios de diseño orientado a objetos
- **DRY**: Don't Repeat Yourself
- **KISS**: Keep It Simple, Stupid
- **Separation of Concerns**: Separación clara de responsabilidades
- **Dependency Inversion**: Dependencias siempre hacia el dominio

## Módulos del Sistema

### 1. Cliente
**Ruta**: `src/Cliente/`

Gestión de clientes que alquilan trasteros.

- **Entidad**: Cliente
- **Value Objects**: ClienteId, DniNie, Email, Telefono
- **Endpoints**: `/api/clientes`

[Ver documentación completa](Cliente/README.md)

---

### 2. Direccion
**Ruta**: `src/Direccion/`

Gestión de direcciones físicas para locales.

- **Entidad**: Direccion
- **Value Objects**: DireccionId, CodigoPostal, Coordenadas
- **Endpoints**: `/api/direcciones`

[Ver documentación completa](Direccion/README.md)

---

### 3. Local
**Ruta**: `src/Local/`

Gestión de locales físicos que contienen trasteros.

- **Entidad**: Local
- **Value Objects**: LocalId, ReferenciaCatastral
- **Endpoints**: `/api/locales`

[Ver documentación completa](Local/README.md)

---

### 4. Trastero
**Ruta**: `src/Trastero/`

Gestión de trasteros individuales dentro de los locales.

- **Entidad**: Trastero
- **Value Objects**: TrasteroId, Superficie, PrecioMensual
- **Enums**: TrasteroEstado (disponible, ocupado, mantenimiento, reservado)
- **Endpoints**: `/api/trasteros`

[Ver documentación completa](Trastero/README.md)

---

### 5. Contrato
**Ruta**: `src/Contrato/`

Gestión de contratos de alquiler entre clientes y trasteros.

- **Entidad**: Contrato
- **Value Objects**: ContratoId, PrecioMensual, Fianza
- **Enums**: ContratoEstado (activo, finalizado, cancelado, pendiente)
- **Endpoints**: `/api/contratos`

[Ver documentación completa](Contrato/README.md)

---

### 6. Ingreso
**Ruta**: `src/Ingreso/`

Gestión de ingresos económicos asociados a contratos.

- **Entidad**: Ingreso
- **Value Objects**: IngresoId, Importe
- **Enums**: IngresoCategoria, MetodoPago
- **Endpoints**: `/api/ingresos`

[Ver documentación completa](Ingreso/README.md)

---

### 7. Gasto
**Ruta**: `src/Gasto/`

Gestión de gastos operacionales de los locales.

- **Entidad**: Gasto
- **Value Objects**: GastoId, Importe
- **Enums**: GastoCategoria, MetodoPago
- **Endpoints**: `/api/gastos`

[Ver documentación completa](Gasto/README.md)

---

### 8. Prestamo
**Ruta**: `src/Prestamo/`

Gestión de préstamos bancarios asociados a locales.

- **Entidad**: Prestamo
- **Value Objects**: PrestamoId, CapitalSolicitado, TotalADevolver, TipoInteres
- **Enums**: PrestamoEstado (activo, cancelado, finalizado)
- **Endpoints**: `/api/prestamos`

[Ver documentación completa](Prestamo/README.md)

---

## Tecnologías Utilizadas

- **PHP 8.2+**: Lenguaje de programación
- **Symfony 7.4**: Framework web
- **Doctrine ORM**: Persistencia de datos
- **Symfony Messenger**: CQRS (Commands/Queries)
- **MySQL/MariaDB**: Base de datos relacional
- **Symfony Validator**: Validaciones
- **Doctrine Migrations**: Versionado de base de datos

## Configuración del Proyecto

### Requisitos

- PHP 8.2 o superior
- Composer
- MySQL/MariaDB
- Extensiones PHP: PDO, pdo_mysql, intl, json

### Instalación

```bash
# 1. Clonar el repositorio
git clone <repository-url>
cd trasteros/api

# 2. Instalar dependencias
composer install

# 3. Configurar variables de entorno
cp .env .env.local
# Editar .env.local con tus credenciales de base de datos

# 4. Crear base de datos
php bin/console doctrine:database:create

# 5. Ejecutar migraciones
php bin/console doctrine:migrations:migrate

# 6. (Opcional) Cargar datos de prueba
php bin/console doctrine:fixtures:load

# 7. Iniciar servidor de desarrollo
symfony server:start
# o
php -S localhost:8000 -t public/
```

### Variables de Entorno

```env
# .env.local
DATABASE_URL="mysql://user:password@127.0.0.1:3306/trasteros?serverVersion=8.0"
APP_ENV=dev
APP_SECRET=your-secret-key
```

## Convenciones de Código

### Nomenclatura

- **Módulos**: PascalCase singular (Cliente, Contrato, Ingreso)
- **Entidades**: PascalCase singular (Cliente, Trastero)
- **Value Objects**: PascalCase descriptivo (ClienteId, DniNie, Email)
- **Commands**: Verbo + Entidad (CreateCliente, UpdateContrato)
- **Queries**: Verbo + Entidad (FindCliente, ListContratos)
- **Eventos**: Entidad + Verbo en pasado (ClienteCreated, ContratoFinalizado)
- **Excepciones**: Entidad + Error + Exception (ClienteNotFoundException)

### Estructura de Archivos

```php
<?php

declare(strict_types=1);  // SIEMPRE en todos los archivos

namespace App\[Modulo]\[Capa]\[Subcarpeta];

// Imports organizados: externos primero, luego propios

/**
 * Documentación de la clase
 */
class MiClase
{
    // Propiedades privadas con tipado estricto
    // Constructor privado para forzar named constructors
    // Named constructors estáticos públicos
    // Métodos públicos
    // Métodos privados
}
```

### API REST

Todos los módulos siguen convenciones REST estrictas:

- **GET /api/recursos**: Listar recursos (200)
- **GET /api/recursos/{id}**: Obtener recurso (200, 404)
- **POST /api/recursos**: Crear recurso (201, 400)
- **PUT /api/recursos/{id}**: Actualizar recurso (200, 404, 400)
- **DELETE /api/recursos/{id}**: Eliminar recurso (204, 404)

#### Formato de Respuestas

**Éxito (recurso único)**:
```json
{
  "id": 1,
  "nombre": "Valor",
  ...
}
```

**Éxito (colección)**:
```json
{
  "data": [...],
  "meta": {
    "total": 100
  }
}
```

**Error**:
```json
{
  "error": {
    "message": "Mensaje descriptivo",
    "code": "ERROR_CODE"
  }
}
```

**Error de validación**:
```json
{
  "error": {
    "message": "Validation failed",
    "code": "VALIDATION_ERROR",
    "details": {
      "campo": ["Error 1", "Error 2"]
    }
  }
}
```

## Características Comunes

### Auditoría

Todas las entidades incluyen campos de auditoría:

- `created_at`: Fecha de creación
- `created_by`: Usuario que creó
- `updated_at`: Fecha de última actualización
- `updated_by`: Usuario que actualizó
- `deleted_at`: Fecha de eliminación (soft delete)
- `deleted_by`: Usuario que eliminó

### Soft Delete

Las entidades no se eliminan físicamente de la base de datos, solo se marca `deleted_at`.

### Eventos de Dominio

Cada operación importante emite eventos:

- `[Entidad]Created`: Al crear
- `[Entidad]Updated`: Al actualizar
- `[Entidad]Deleted`: Al eliminar

### CQRS

Separación estricta:

- **Commands**: Modifican estado (Create, Update, Delete)
- **Queries**: Solo lectura (Find, List)

Ambos se ejecutan a través de Symfony Messenger:

```php
// En el controlador
$result = $this->messageBus->dispatch($command);
```

## Testing

### Ejecutar Tests

```bash
# Todos los tests
php bin/phpunit

# Tests de un módulo específico
php bin/phpunit tests/Cliente

# Tests con cobertura
php bin/phpunit --coverage-html var/coverage
```

### Probar API

Ver archivos `TESTING.md` en cada módulo para ejemplos de pruebas con cURL.

## Migraciones de Base de Datos

### Crear Nueva Migración

```bash
# Generar migración desde cambios en entidades
php bin/console doctrine:migrations:diff

# Crear migración vacía
php bin/console doctrine:migrations:generate
```

### Ejecutar Migraciones

```bash
# Ejecutar migraciones pendientes
php bin/console doctrine:migrations:migrate

# Ver estado de migraciones
php bin/console doctrine:migrations:status

# Rollback última migración
php bin/console doctrine:migrations:migrate prev
```

## Comandos Útiles

```bash
# Limpiar caché
php bin/console cache:clear

# Ver rutas registradas
php bin/console debug:router

# Ver servicios registrados
php bin/console debug:container

# Ver configuración de un bundle
php bin/console debug:config doctrine

# Validar esquema de base de datos
php bin/console doctrine:schema:validate

# Ver SQL de las migraciones
php bin/console doctrine:migrations:migrate --dry-run
```

## Añadir Nuevo Módulo

Para añadir un nuevo módulo, sigue esta estructura:

```bash
src/[NuevoModulo]/
├── Domain/
│   ├── Model/
│   │   ├── [Entidad].php
│   │   └── [Entidad]Id.php
│   ├── Repository/
│   │   └── [Entidad]RepositoryInterface.php
│   ├── Event/
│   ├── Exception/
│   └── Service/
├── Application/
│   ├── Command/
│   ├── Query/
│   └── DTO/
├── Infrastructure/
│   ├── Persistence/Doctrine/Repository/
│   ├── Controller/
│   └── EventSubscriber/
├── README.md
└── TESTING.md
```

1. Crear la estructura de carpetas
2. Implementar el dominio (Entidad, Value Objects, Repository Interface)
3. Implementar casos de uso (Commands, Queries, Handlers)
4. Implementar infraestructura (Repository, Controller)
5. Crear migración de base de datos
6. Registrar servicios en `config/services.yaml`
7. Documentar en README.md y TESTING.md

## Contribuir

1. Fork el proyecto
2. Crear rama feature (`git checkout -b feature/nueva-funcionalidad`)
3. Commit cambios (`git commit -m 'feat: añadir nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Abrir Pull Request

### Convenciones de Commits

Seguimos [Conventional Commits](https://www.conventionalcommits.org/):

- `feat:` Nueva funcionalidad
- `fix:` Corrección de bug
- `docs:` Cambios en documentación
- `refactor:` Refactorización de código
- `test:` Añadir o modificar tests
- `chore:` Cambios en build, CI, etc.

## Soporte

Para preguntas o problemas:

1. Revisar documentación de cada módulo
2. Consultar issues existentes
3. Crear nuevo issue con descripción detallada

## Licencia

[Incluir licencia del proyecto]

## Equipo

[Incluir información del equipo de desarrollo]
