# Módulo Prestamo - Resumen de Implementación

## Descripción General

El módulo **Prestamo** gestiona los préstamos bancarios asociados a los locales. Implementa una arquitectura hexagonal completa con CQRS, siguiendo los patrones del proyecto.

## Estructura del Módulo

```
src/Prestamo/
├── Domain/
│   ├── Model/
│   │   ├── Prestamo.php                    # Entidad principal con Doctrine ORM
│   │   ├── PrestamoId.php                  # Value Object para ID
│   │   ├── PrestamoEstado.php              # Enum (activo, cancelado, finalizado)
│   │   ├── CapitalSolicitado.php           # Value Object con validación
│   │   ├── TotalADevolver.php              # Value Object con validación
│   │   └── TipoInteres.php                 # Value Object con validación
│   ├── Repository/
│   │   └── PrestamoRepositoryInterface.php # Contrato del repositorio
│   ├── Exception/
│   │   ├── PrestamoNotFoundException.php
│   │   ├── InvalidCapitalSolicitadoException.php
│   │   ├── InvalidTotalADevolverException.php
│   │   ├── InvalidTipoInteresException.php
│   │   └── InvalidPrestamoEstadoException.php
│   └── Event/
│       ├── PrestamoCreated.php             # Evento de dominio
│       ├── PrestamoUpdated.php             # Evento de dominio
│       └── PrestamoDeleted.php             # Evento de dominio
├── Application/
│   ├── DTO/
│   │   ├── PrestamoRequest.php             # DTO de entrada con validaciones
│   │   └── PrestamoResponse.php            # DTO de salida
│   ├── Command/
│   │   ├── CreatePrestamo/
│   │   │   ├── CreatePrestamoCommand.php
│   │   │   └── CreatePrestamoCommandHandler.php
│   │   ├── UpdatePrestamo/
│   │   │   ├── UpdatePrestamoCommand.php
│   │   │   └── UpdatePrestamoCommandHandler.php
│   │   └── DeletePrestamo/
│   │       ├── DeletePrestamoCommand.php
│   │       └── DeletePrestamoCommandHandler.php
│   └── Query/
│       ├── FindPrestamo/
│       │   ├── FindPrestamoQuery.php
│       │   └── FindPrestamoQueryHandler.php
│       └── ListPrestamos/
│           ├── ListPrestamosQuery.php
│           └── ListPrestamosQueryHandler.php
└── Infrastructure/
    ├── Persistence/Doctrine/Repository/
    │   └── DoctrinePrestamoRepository.php  # Implementación Doctrine
    └── Controller/
        └── PrestamoController.php          # API REST Controller

migrations/
└── Version20260201000008.php               # Migración de base de datos
```

## Modelo de Datos

### Tabla: `prestamo`

```sql
CREATE TABLE prestamo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    local_id INT NOT NULL,
    entidad_bancaria VARCHAR(255) DEFAULT NULL,
    numero_prestamo VARCHAR(100) DEFAULT NULL,
    capital_solicitado DECIMAL(12,2) NOT NULL,
    total_a_devolver DECIMAL(12,2) NOT NULL,
    tipo_interes DECIMAL(5,4) DEFAULT NULL,
    fecha_concesion DATE NOT NULL,
    estado VARCHAR(50) NOT NULL DEFAULT 'activo',
    created_at DATETIME NOT NULL,
    created_by INT DEFAULT NULL,
    updated_at DATETIME NOT NULL,
    updated_by INT DEFAULT NULL,
    deleted_at DATETIME DEFAULT NULL,
    deleted_by INT DEFAULT NULL,
    FOREIGN KEY (local_id) REFERENCES local(id),
    FOREIGN KEY (created_by) REFERENCES usuario(id),
    FOREIGN KEY (updated_by) REFERENCES usuario(id),
    FOREIGN KEY (deleted_by) REFERENCES usuario(id)
);
```

### Campos Principales

- **id**: Identificador único autoincremental
- **local_id**: Relación con el local (FK)
- **entidad_bancaria**: Nombre del banco (opcional)
- **numero_prestamo**: Número de referencia del préstamo (opcional)
- **capital_solicitado**: Cantidad solicitada (DECIMAL 12,2)
- **total_a_devolver**: Cantidad total a devolver (DECIMAL 12,2)
- **tipo_interes**: Tasa de interés (DECIMAL 5,4, opcional)
- **fecha_concesion**: Fecha de concesión del préstamo
- **estado**: Estado del préstamo (activo, cancelado, finalizado)

### Auditoría y Soft Delete

- **created_at**, **created_by**: Registro de creación
- **updated_at**, **updated_by**: Registro de actualización
- **deleted_at**, **deleted_by**: Soft delete

## API REST Endpoints

### Base URL: `/api/prestamos`

#### 1. Listar Préstamos
```http
GET /api/prestamos
```

**Query Parameters:**
- `localId` (int, optional): Filtrar por local
- `estado` (string, optional): Filtrar por estado (activo, cancelado, finalizado)
- `entidadBancaria` (string, optional): Buscar por entidad bancaria
- `onlyActive` (boolean, optional): Solo préstamos activos

**Respuesta 200:**
```json
{
  "data": [
    {
      "id": 1,
      "localId": 5,
      "localNombre": "Local Centro",
      "entidadBancaria": "Banco Santander",
      "numeroPrestamo": "PRE-2024-001",
      "capitalSolicitado": 150000.00,
      "totalADevolver": 180000.00,
      "tipoInteres": 3.5000,
      "fechaConcesion": "2024-01-15",
      "estado": "activo",
      "createdAt": "2024-01-15 10:30:00",
      "updatedAt": "2024-01-15 10:30:00",
      "deletedAt": null
    }
  ],
  "meta": {
    "total": 1
  }
}
```

#### 2. Obtener Préstamo
```http
GET /api/prestamos/{id}
```

**Respuesta 200:**
```json
{
  "id": 1,
  "localId": 5,
  "localNombre": "Local Centro",
  "entidadBancaria": "Banco Santander",
  "numeroPrestamo": "PRE-2024-001",
  "capitalSolicitado": 150000.00,
  "totalADevolver": 180000.00,
  "tipoInteres": 3.5000,
  "fechaConcesion": "2024-01-15",
  "estado": "activo",
  "createdAt": "2024-01-15 10:30:00",
  "updatedAt": "2024-01-15 10:30:00",
  "deletedAt": null
}
```

**Respuesta 404:**
```json
{
  "error": {
    "message": "Prestamo with id 999 not found",
    "code": "PRESTAMO_NOT_FOUND"
  }
}
```

#### 3. Crear Préstamo
```http
POST /api/prestamos
Content-Type: application/json
```

**Cuerpo de la Petición:**
```json
{
  "localId": 5,
  "entidadBancaria": "Banco Santander",
  "numeroPrestamo": "PRE-2024-001",
  "capitalSolicitado": 150000.00,
  "totalADevolver": 180000.00,
  "tipoInteres": 3.5000,
  "fechaConcesion": "2024-01-15",
  "estado": "activo"
}
```

**Validaciones:**
- `localId`: Obligatorio, número positivo
- `capitalSolicitado`: Obligatorio, número positivo, máx. 999999999.99
- `totalADevolver`: Obligatorio, número positivo, máx. 999999999.99
- `tipoInteres`: Opcional, número >= 0, máx. 99.9999
- `fechaConcesion`: Obligatorio, formato Y-m-d
- `estado`: Opcional, valores permitidos: activo, cancelado, finalizado
- `entidadBancaria`: Opcional, máx. 255 caracteres
- `numeroPrestamo`: Opcional, máx. 100 caracteres

**Respuesta 201:**
```json
{
  "id": 1,
  "localId": 5,
  "localNombre": "Local Centro",
  ...
}
```

**Respuesta 400 (Error de Validación):**
```json
{
  "error": {
    "message": "Validation failed",
    "code": "VALIDATION_ERROR",
    "details": {
      "capitalSolicitado": ["El capital solicitado es obligatorio"]
    }
  }
}
```

#### 4. Actualizar Préstamo
```http
PUT /api/prestamos/{id}
Content-Type: application/json
```

**Cuerpo de la Petición:**
```json
{
  "localId": 5,
  "entidadBancaria": "BBVA",
  "numeroPrestamo": "PRE-2024-001-MOD",
  "capitalSolicitado": 150000.00,
  "totalADevolver": 175000.00,
  "tipoInteres": 3.2000,
  "fechaConcesion": "2024-01-15",
  "estado": "activo"
}
```

**Respuesta 200:**
```json
{
  "id": 1,
  "localId": 5,
  ...
}
```

**Respuesta 404:**
```json
{
  "error": {
    "message": "Prestamo with id 999 not found",
    "code": "PRESTAMO_NOT_FOUND"
  }
}
```

#### 5. Eliminar Préstamo
```http
DELETE /api/prestamos/{id}
```

**Respuesta 204:** (Sin contenido)

**Respuesta 404:**
```json
{
  "error": {
    "message": "Prestamo with id 999 not found",
    "code": "PRESTAMO_NOT_FOUND"
  }
}
```

## Value Objects

### CapitalSolicitado
- Rango válido: 0.01 - 999999999.99
- Validación en constructor
- Métodos: `equals()`, `isGreaterThan()`, `isLessThan()`

### TotalADevolver
- Rango válido: 0.01 - 999999999.99
- Validación en constructor
- Métodos: `equals()`, `isGreaterThan()`, `isLessThan()`

### TipoInteres
- Rango válido: 0 - 99.9999
- Validación en constructor
- Métodos: `equals()`

## Estados del Préstamo

```php
enum PrestamoEstado: string
{
    case ACTIVO = 'activo';
    case CANCELADO = 'cancelado';
    case FINALIZADO = 'finalizado';
}
```

## Casos de Uso

### Commands (Escritura)
1. **CreatePrestamoCommand**: Crear un nuevo préstamo
2. **UpdatePrestamoCommand**: Actualizar un préstamo existente
3. **DeletePrestamoCommand**: Eliminar un préstamo

### Queries (Lectura)
1. **FindPrestamoQuery**: Obtener un préstamo por ID
2. **ListPrestamosQuery**: Listar préstamos con filtros

## Repositorio

### Métodos Disponibles

```php
interface PrestamoRepositoryInterface
{
    public function save(Prestamo $prestamo): void;
    public function remove(Prestamo $prestamo): void;
    public function findById(PrestamoId $id): ?Prestamo;
    public function findAll(): array;
    public function findActivePrestamos(): array;
    public function findByLocalId(int $localId): array;
    public function findByEstado(string $estado): array;
    public function findByEntidadBancaria(string $entidadBancaria): array;
    public function count(): int;
}
```

## Configuración

### services.yaml

El repositorio está registrado en `D:\Code\trasteros\api\config\services.yaml`:

```yaml
# Prestamo module - Repository binding
App\Prestamo\Domain\Repository\PrestamoRepositoryInterface:
    class: App\Prestamo\Infrastructure\Persistence\Doctrine\Repository\DoctrinePrestamoRepository
```

## Migración de Base de Datos

Archivo: `D:\Code\trasteros\api\migrations\Version20260201000008.php`

Para ejecutar la migración:

```bash
cd api
php bin/console doctrine:migrations:migrate
```

## Ejemplos de Uso

### Crear un Préstamo

```bash
curl -X POST http://localhost:8000/api/prestamos \
  -H "Content-Type: application/json" \
  -d '{
    "localId": 1,
    "entidadBancaria": "Banco Santander",
    "numeroPrestamo": "PRE-2024-001",
    "capitalSolicitado": 200000.00,
    "totalADevolver": 240000.00,
    "tipoInteres": 4.5000,
    "fechaConcesion": "2024-01-20",
    "estado": "activo"
  }'
```

### Listar Préstamos de un Local

```bash
curl http://localhost:8000/api/prestamos?localId=1
```

### Listar Préstamos por Estado

```bash
curl http://localhost:8000/api/prestamos?estado=activo
```

### Actualizar un Préstamo

```bash
curl -X PUT http://localhost:8000/api/prestamos/1 \
  -H "Content-Type: application/json" \
  -d '{
    "localId": 1,
    "entidadBancaria": "BBVA",
    "numeroPrestamo": "PRE-2024-001",
    "capitalSolicitado": 200000.00,
    "totalADevolver": 235000.00,
    "tipoInteres": 4.2000,
    "fechaConcesion": "2024-01-20",
    "estado": "activo"
  }'
```

### Eliminar un Préstamo

```bash
curl -X DELETE http://localhost:8000/api/prestamos/1
```

## Características Implementadas

- Arquitectura Hexagonal completa
- CQRS (Commands y Queries separados)
- Value Objects con validaciones
- Enumeración de estados
- Repository Pattern
- DTOs para entrada/salida
- Validaciones robustas
- Manejo de errores con códigos HTTP apropiados
- Soft delete (estructura preparada)
- Auditoría (created_by, updated_by, deleted_by)
- Eventos de dominio
- API REST siguiendo mejores prácticas
- Filtros y búsquedas en el repositorio

## Archivos Creados

Total: **28 archivos PHP + 1 migración**

### Domain (11 archivos)
- 6 Models (Prestamo, PrestamoId, PrestamoEstado, CapitalSolicitado, TotalADevolver, TipoInteres)
- 1 Repository Interface
- 3 Events
- 5 Exceptions

### Application (12 archivos)
- 2 DTOs
- 6 Commands (3 comandos + 3 handlers)
- 4 Queries (2 queries + 2 handlers)

### Infrastructure (2 archivos)
- 1 Repository Implementation
- 1 Controller

### Migrations (1 archivo)
- Version20260201000008.php

## Notas Técnicas

1. **Tipado Estricto**: Todos los archivos usan `declare(strict_types=1)`
2. **Readonly Properties**: Los DTOs y Value Objects usan `readonly`
3. **Named Constructors**: Los Value Objects usan factories estáticas
4. **Messenger Component**: Los handlers usan `#[AsMessageHandler]`
5. **Doctrine ORM**: La entidad usa atributos PHP 8
6. **Validación**: Se usa Symfony Validator en los DTOs

## Próximos Pasos Opcionales

1. Implementar EventSubscribers para los eventos de dominio
2. Añadir soft delete real (requiere contexto de seguridad para obtener el usuario actual)
3. Añadir paginación en el endpoint de listado
4. Implementar filtros avanzados (fecha desde/hasta, ordenamiento)
5. Añadir tests unitarios y de integración
6. Implementar cálculos de amortización
7. Añadir relación con pagos/cuotas del préstamo
