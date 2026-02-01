# Módulo Gasto

Módulo completo para la gestión de gastos asociados a locales, implementado con arquitectura hexagonal y CQRS.

## Estructura del Módulo

```
Gasto/
├── Application/
│   ├── Command/
│   │   ├── CreateGasto/
│   │   │   ├── CreateGastoCommand.php
│   │   │   └── CreateGastoCommandHandler.php
│   │   ├── UpdateGasto/
│   │   │   ├── UpdateGastoCommand.php
│   │   │   └── UpdateGastoCommandHandler.php
│   │   └── DeleteGasto/
│   │       ├── DeleteGastoCommand.php
│   │       └── DeleteGastoCommandHandler.php
│   ├── Query/
│   │   ├── FindGasto/
│   │   │   ├── FindGastoQuery.php
│   │   │   └── FindGastoQueryHandler.php
│   │   └── ListGastos/
│   │       ├── ListGastosQuery.php
│   │       └── ListGastosQueryHandler.php
│   └── DTO/
│       ├── GastoRequest.php
│       └── GastoResponse.php
├── Domain/
│   ├── Model/
│   │   ├── Gasto.php                    # Entidad principal
│   │   ├── GastoId.php                  # Value Object
│   │   ├── Importe.php                  # Value Object con validación
│   │   ├── GastoCategoria.php           # Enum backed (string)
│   │   └── MetodoPago.php               # Enum backed (string)
│   ├── Repository/
│   │   └── GastoRepositoryInterface.php
│   ├── Event/
│   │   └── GastoCreated.php
│   └── Exception/
│       ├── GastoNotFoundException.php
│       ├── InvalidImporteException.php
│       ├── InvalidGastoCategoriaException.php
│       └── InvalidMetodoPagoException.php
└── Infrastructure/
    ├── Persistence/Doctrine/Repository/
    │   └── DoctrineGastoRepository.php
    └── Controller/
        └── GastoController.php
```

## API REST Endpoints

### Listar Gastos
```http
GET /api/gastos
GET /api/gastos?localId=1
GET /api/gastos?categoria=suministros
GET /api/gastos?desde=2024-01-01&hasta=2024-12-31
GET /api/gastos?localId=1&desde=2024-01-01&hasta=2024-12-31
GET /api/gastos?onlyActive=true
```

**Respuesta 200:**
```json
{
  "data": [
    {
      "id": 1,
      "localId": 1,
      "localNombre": "Trasteros Centro",
      "concepto": "Luz enero 2024",
      "descripcion": "Recibo mensual electricidad",
      "importe": 125.50,
      "fecha": "2024-01-15",
      "categoria": "suministros",
      "metodoPago": "domiciliacion",
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

### Obtener un Gasto
```http
GET /api/gastos/{id}
```

**Respuesta 200:**
```json
{
  "id": 1,
  "localId": 1,
  "localNombre": "Trasteros Centro",
  "concepto": "Luz enero 2024",
  "descripcion": "Recibo mensual electricidad",
  "importe": 125.50,
  "fecha": "2024-01-15",
  "categoria": "suministros",
  "metodoPago": "domiciliacion",
  "createdAt": "2024-01-15 10:30:00",
  "updatedAt": "2024-01-15 10:30:00",
  "deletedAt": null
}
```

**Respuesta 404:**
```json
{
  "error": {
    "message": "Gasto with id 999 not found",
    "code": "GASTO_NOT_FOUND"
  }
}
```

### Crear un Gasto
```http
POST /api/gastos
Content-Type: application/json

{
  "localId": 1,
  "concepto": "Luz enero 2024",
  "descripcion": "Recibo mensual electricidad",
  "importe": 125.50,
  "fecha": "2024-01-15",
  "categoria": "suministros",
  "metodoPago": "domiciliacion"
}
```

**Respuesta 201:** (igual formato que GET)

**Respuesta 400 (Validación):**
```json
{
  "error": {
    "message": "Validation failed",
    "code": "VALIDATION_ERROR",
    "details": {
      "concepto": ["El concepto es obligatorio"],
      "importe": ["El importe debe ser un número positivo"]
    }
  }
}
```

### Actualizar un Gasto
```http
PUT /api/gastos/{id}
Content-Type: application/json

{
  "localId": 1,
  "concepto": "Luz enero 2024 - Rectificado",
  "descripcion": "Recibo mensual electricidad con corrección",
  "importe": 130.00,
  "fecha": "2024-01-15",
  "categoria": "suministros",
  "metodoPago": "domiciliacion"
}
```

**Respuesta 200:** (igual formato que GET)
**Respuesta 404:** (si no existe)
**Respuesta 400:** (errores de validación)

### Eliminar un Gasto
```http
DELETE /api/gastos/{id}
```

**Respuesta 204:** (sin contenido)
**Respuesta 404:** (si no existe)

## Enums

### GastoCategoria
```php
- SUMINISTROS = 'suministros'
- SEGUROS = 'seguros'
- IMPUESTOS = 'impuestos'
- MANTENIMIENTO = 'mantenimiento'
- PRESTAMO = 'prestamo'
- GESTORIA = 'gestoria'
- OTROS = 'otros'
```

### MetodoPago
```php
- EFECTIVO = 'efectivo'
- TRANSFERENCIA = 'transferencia'
- TARJETA = 'tarjeta'
- DOMICILIACION = 'domiciliacion'
```

## Value Objects

### Importe
- Validación: no puede ser negativo
- Validación: no puede superar 9999999.99
- Métodos: add(), subtract(), isGreaterThan(), isLessThan(), equals()

### GastoId
- Encapsula el ID del gasto
- Métodos: equals(), __toString()

## Métodos Útiles del Repositorio

```php
// Búsquedas básicas
findById(GastoId $id): ?Gasto
findAll(): array
findActiveGastos(): array

// Búsquedas por filtros
findByLocalId(int $localId): array
findByCategoria(GastoCategoria $categoria): array
findByDateRange(DateTimeImmutable $desde, DateTimeImmutable $hasta): array
findByLocalAndDateRange(int $localId, DateTimeImmutable $desde, DateTimeImmutable $hasta): array
findByLocalAndCategoria(int $localId, GastoCategoria $categoria): array

// Totales
getTotalImporteByLocal(int $localId): float
getTotalImporteByLocalAndCategoria(int $localId, GastoCategoria $categoria): float
getTotalImporteByLocalAndDateRange(int $localId, DateTimeImmutable $desde, DateTimeImmutable $hasta): float
```

## Eventos de Dominio

### GastoCreated
Se dispara cuando se crea un nuevo gasto. Contiene:
- gastoId
- localId
- importe
- categoria
- fecha
- occurredOn

## Modelo de Datos SQL

```sql
CREATE TABLE gasto (
    id INT AUTO_INCREMENT PRIMARY KEY,
    local_id INT NOT NULL,
    concepto VARCHAR(255) NOT NULL,
    descripcion TEXT,
    importe DECIMAL(10,2) NOT NULL,
    fecha DATE NOT NULL,
    categoria ENUM('suministros', 'seguros', 'impuestos', 'mantenimiento', 'prestamo', 'gestoria', 'otros') NOT NULL,
    metodo_pago ENUM('efectivo', 'transferencia', 'tarjeta', 'domiciliacion'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by INT,
    deleted_at TIMESTAMP NULL,
    deleted_by INT,
    FOREIGN KEY (local_id) REFERENCES local(id),
    FOREIGN KEY (created_by) REFERENCES usuario(id),
    FOREIGN KEY (updated_by) REFERENCES usuario(id),
    FOREIGN KEY (deleted_by) REFERENCES usuario(id)
);
```

## Configuración

El repositorio está configurado en `config/services.yaml`:

```yaml
App\Gasto\Domain\Repository\GastoRepositoryInterface:
    class: App\Gasto\Infrastructure\Persistence\Doctrine\Repository\DoctrineGastoRepository
```

## Ejemplo de Uso desde Código

```php
use App\Gasto\Application\Command\CreateGasto\CreateGastoCommand;
use Symfony\Component\Messenger\MessageBusInterface;

// Crear un gasto
$command = new CreateGastoCommand(
    localId: 1,
    concepto: 'IBI 2024',
    importe: 450.00,
    fecha: '2024-01-10',
    categoria: 'impuestos',
    descripcion: 'Impuesto de Bienes Inmuebles',
    metodoPago: 'transferencia'
);

$envelope = $messageBus->dispatch($command);
$gasto = $envelope->last(HandledStamp::class)->getResult();
```
