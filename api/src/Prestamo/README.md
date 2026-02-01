# Módulo Préstamo

Gestión de préstamos bancarios asociados a locales.

## Descripción

El módulo Préstamo permite gestionar los préstamos bancarios que se utilizan para financiar la compra o mejoras de los locales donde se ubican los trasteros. Incluye información sobre la entidad bancaria, capital solicitado, total a devolver, tipo de interés y estado del préstamo.

## Estructura del Módulo

```
src/Prestamo/
├── Domain/
│   ├── Model/
│   │   ├── Prestamo.php                   # Entidad principal
│   │   ├── PrestamoId.php                 # Value Object para ID
│   │   ├── CapitalSolicitado.php          # Value Object para capital
│   │   ├── TotalADevolver.php             # Value Object para total
│   │   ├── TipoInteres.php                # Value Object para interés
│   │   └── PrestamoEstado.php             # Enum de estados
│   ├── Repository/
│   │   └── PrestamoRepositoryInterface.php
│   ├── Exception/
│   │   ├── PrestamoNotFoundException.php
│   │   ├── InvalidCapitalSolicitadoException.php
│   │   ├── InvalidTotalADevolverException.php
│   │   ├── InvalidTipoInteresException.php
│   │   └── InvalidPrestamoEstadoException.php
│   └── Event/
│       ├── PrestamoCreated.php
│       ├── PrestamoUpdated.php
│       └── PrestamoDeleted.php
├── Application/
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
│   ├── Query/
│   │   ├── FindPrestamo/
│   │   │   ├── FindPrestamoQuery.php
│   │   │   └── FindPrestamoQueryHandler.php
│   │   └── ListPrestamos/
│   │       ├── ListPrestamosQuery.php
│   │       └── ListPrestamosQueryHandler.php
│   └── DTO/
│       ├── PrestamoRequest.php
│       └── PrestamoResponse.php
└── Infrastructure/
    ├── Persistence/Doctrine/Repository/
    │   └── DoctrinePrestamoRepository.php
    └── Controller/
        └── PrestamoController.php
```

## Modelo de Datos

### Entidad: Prestamo

**Tabla**: `prestamo`

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT | Identificador único autoincremental |
| local_id | INT | Referencia al local (FK) |
| entidad_bancaria | VARCHAR(255) | Nombre del banco (opcional) |
| numero_prestamo | VARCHAR(100) | Número de referencia del préstamo (opcional) |
| capital_solicitado | DECIMAL(12,2) | Cantidad solicitada |
| total_a_devolver | DECIMAL(12,2) | Cantidad total a devolver |
| tipo_interes | DECIMAL(5,4) | Tasa de interés (opcional) |
| fecha_concesion | DATE | Fecha de concesión |
| estado | VARCHAR(50) | Estado del préstamo |
| created_at | DATETIME | Fecha de creación |
| created_by | INT | Usuario que creó (FK a usuario) |
| updated_at | DATETIME | Fecha de última actualización |
| updated_by | INT | Usuario que actualizó (FK a usuario) |
| deleted_at | DATETIME | Fecha de eliminación (soft delete) |
| deleted_by | INT | Usuario que eliminó (FK a usuario) |

**Índices**:
- `INDEX(local_id)` - Para búsquedas por local
- `INDEX(estado)` - Para filtrar por estado
- `INDEX(fecha_concesion)` - Para ordenar por fecha
- `INDEX(deleted_at)` - Para soft delete

### Value Objects

#### PrestamoId
- **Propósito**: Identidad única del préstamo
- **Validación**: Entero positivo
- **Métodos**: `fromInt()`, `value()`, `equals()`

#### CapitalSolicitado
- **Propósito**: Cantidad de dinero solicitada al banco
- **Validación**: Número positivo, rango 0.01 - 999,999,999.99
- **Métodos**: `fromFloat()`, `value`, `isGreaterThan()`, `isLessThan()`, `equals()`

#### TotalADevolver
- **Propósito**: Cantidad total a devolver (capital + intereses)
- **Validación**: Número positivo, rango 0.01 - 999,999,999.99
- **Métodos**: `fromFloat()`, `value`, `isGreaterThan()`, `isLessThan()`, `equals()`

#### TipoInteres
- **Propósito**: Porcentaje de interés del préstamo
- **Validación**: Número >= 0, rango 0 - 99.9999
- **Métodos**: `fromFloat()`, `value`, `equals()`

### Enumeración: PrestamoEstado

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

#### CreatePrestamo
Crea un nuevo préstamo bancario.

**Parámetros**:
- `localId` (int, obligatorio)
- `entidadBancaria` (string, opcional)
- `numeroPrestamo` (string, opcional)
- `capitalSolicitado` (float, obligatorio)
- `totalADevolver` (float, obligatorio)
- `tipoInteres` (float, opcional)
- `fechaConcesion` (string, obligatorio, formato: Y-m-d)
- `estado` (string, opcional, default: 'activo')

**Validaciones**:
- Local debe existir
- Capital solicitado > 0 y <= 999,999,999.99
- Total a devolver > 0 y <= 999,999,999.99
- Tipo de interés >= 0 y <= 99.9999
- Fecha en formato válido
- Estado válido (activo, cancelado, finalizado)

**Eventos emitidos**: `PrestamoCreated`

---

#### UpdatePrestamo
Actualiza un préstamo existente.

**Parámetros**: Los mismos que CreatePrestamo más `id`

**Validaciones**: Las mismas que CreatePrestamo

**Eventos emitidos**: `PrestamoUpdated`

---

#### DeletePrestamo
Elimina un préstamo (soft delete).

**Parámetros**:
- `id` (int, obligatorio)

**Eventos emitidos**: `PrestamoDeleted`

---

### Queries (Lectura)

#### FindPrestamo
Busca un préstamo por ID.

**Parámetros**:
- `id` (int, obligatorio)

**Retorna**: `PrestamoResponse` o `PrestamoNotFoundException`

---

#### ListPrestamos
Lista préstamos con filtros opcionales.

**Parámetros**:
- `localId` (int, opcional): Filtrar por local
- `estado` (string, opcional): Filtrar por estado
- `entidadBancaria` (string, opcional): Buscar por entidad bancaria
- `onlyActive` (bool, opcional): Solo préstamos no eliminados

**Retorna**: Array de `PrestamoResponse`

---

## Repositorio

### Métodos Disponibles

```php
interface PrestamoRepositoryInterface
{
    // CRUD básico
    public function save(Prestamo $prestamo): void;
    public function remove(Prestamo $prestamo): void;
    public function findById(PrestamoId $id): ?Prestamo;
    public function findAll(): array;

    // Búsquedas filtradas
    public function findActivePrestamos(): array;
    public function findByLocalId(int $localId): array;
    public function findByEstado(string $estado): array;
    public function findByEntidadBancaria(string $entidadBancaria): array;

    // Estadísticas
    public function count(): int;
}
```

## API REST

### Base URL
`/api/prestamos`

### Endpoints

#### 1. Listar Préstamos
```http
GET /api/prestamos
```

**Query Parameters**:
- `localId` (int, opcional): Filtrar por local
- `estado` (string, opcional): Filtrar por estado
- `entidadBancaria` (string, opcional): Buscar por entidad bancaria
- `onlyActive` (boolean, opcional): Solo préstamos activos

**Respuesta 200 OK**:
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
      "createdAt": "2024-01-15T10:30:00+00:00",
      "updatedAt": "2024-01-15T10:30:00+00:00",
      "deletedAt": null
    }
  ],
  "meta": {
    "total": 1
  }
}
```

---

#### 2. Obtener Préstamo
```http
GET /api/prestamos/{id}
```

**Respuesta 200 OK**: Igual que un elemento del listado

**Respuesta 404 Not Found**:
```json
{
  "error": {
    "message": "Prestamo with id 999 not found",
    "code": "PRESTAMO_NOT_FOUND"
  }
}
```

---

#### 3. Crear Préstamo
```http
POST /api/prestamos
Content-Type: application/json
```

**Body**:
```json
{
  "localId": 1,
  "entidadBancaria": "Banco Santander",
  "numeroPrestamo": "PRE-2024-001",
  "capitalSolicitado": 200000.00,
  "totalADevolver": 240000.00,
  "tipoInteres": 4.5000,
  "fechaConcesion": "2024-01-20",
  "estado": "activo"
}
```

**Respuesta 201 Created**: Préstamo creado

**Respuesta 400 Bad Request** (Error de validación):
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

---

#### 4. Actualizar Préstamo
```http
PUT /api/prestamos/{id}
Content-Type: application/json
```

**Body**: Igual que crear

**Respuesta 200 OK**: Préstamo actualizado

---

#### 5. Eliminar Préstamo
```http
DELETE /api/prestamos/{id}
```

**Respuesta 204 No Content**: Préstamo eliminado (soft delete)

---

## Eventos de Dominio

### PrestamoCreated
Emitido cuando se crea un nuevo préstamo.

**Propiedades**:
- `prestamoId` (int)
- `localId` (int)
- `capitalSolicitado` (float)
- `occurredOn` (DateTimeImmutable)

---

### PrestamoUpdated
Emitido cuando se actualiza un préstamo.

**Propiedades**:
- `prestamoId` (int)
- `occurredOn` (DateTimeImmutable)

---

### PrestamoDeleted
Emitido cuando se elimina un préstamo.

**Propiedades**:
- `prestamoId` (int)
- `occurredOn` (DateTimeImmutable)

---

## Configuración

El repositorio debe estar registrado en `config/services.yaml`:

```yaml
App\Prestamo\Domain\Repository\PrestamoRepositoryInterface:
    class: App\Prestamo\Infrastructure\Persistence\Doctrine\Repository\DoctrinePrestamoRepository
```

## Características

- CRUD completo de préstamos bancarios
- Soft delete (no eliminación física)
- Auditoría completa
- Validaciones de rangos numéricos
- Relación con Local
- Filtros por local, estado y entidad bancaria
- Estados de préstamo (activo, cancelado, finalizado)
- Arquitectura hexagonal con CQRS
- Eventos de dominio
- Value Objects para validaciones de negocio

## Próximos Pasos

Para usar este módulo:

1. Ejecutar migraciones: `php bin/console doctrine:migrations:migrate`
2. Verificar rutas: `php bin/console debug:router | grep prestamo`
3. Probar endpoints (ver [TESTING.md](TESTING.md))

## Extensiones Futuras

- Cálculo automático de amortización
- Relación con pagos/cuotas del préstamo
- Alertas de vencimiento de cuotas
- Informes de deuda pendiente
- Comparativas entre entidades bancarias
