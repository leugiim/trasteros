# Módulo Ingreso

Gestión de ingresos asociados a contratos de alquiler de trasteros, siguiendo arquitectura hexagonal con CQRS.

## Estructura

```
Ingreso/
├── Application/          # Capa de Aplicación (Casos de Uso)
│   ├── Command/         # Comandos (Escritura)
│   │   ├── CreateIngreso/
│   │   ├── UpdateIngreso/
│   │   └── DeleteIngreso/
│   ├── Query/           # Consultas (Lectura)
│   │   ├── FindIngreso/
│   │   └── ListIngresos/
│   └── DTO/             # Data Transfer Objects
│       ├── IngresoRequest.php
│       └── IngresoResponse.php
├── Domain/              # Capa de Dominio (Lógica de Negocio)
│   ├── Model/           # Entidades y Value Objects
│   │   ├── Ingreso.php
│   │   ├── IngresoId.php
│   │   ├── Importe.php           # Value Object con validación
│   │   ├── IngresoCategoria.php  # Enum
│   │   └── MetodoPago.php        # Enum
│   ├── Repository/      # Interfaces de Repositorio
│   │   └── IngresoRepositoryInterface.php
│   ├── Event/           # Eventos de Dominio
│   │   ├── IngresoCreated.php
│   │   ├── IngresoUpdated.php
│   │   └── IngresoDeleted.php
│   └── Exception/       # Excepciones de Dominio
│       ├── IngresoNotFoundException.php
│       ├── InvalidImporteException.php
│       ├── InvalidIngresoCategoriaException.php
│       └── InvalidMetodoPagoException.php
└── Infrastructure/      # Capa de Infraestructura
    ├── Controller/
    │   └── IngresoController.php
    └── Persistence/Doctrine/Repository/
        └── DoctrineIngresoRepository.php
```

## API REST Endpoints

### Listar Ingresos
```http
GET /api/ingresos
GET /api/ingresos?contratoId=1
GET /api/ingresos?categoria=mensualidad
GET /api/ingresos?desde=2026-01-01&hasta=2026-12-31
GET /api/ingresos?onlyActive=true
```

**Response 200:**
```json
{
  "data": [
    {
      "id": 1,
      "contratoId": 1,
      "concepto": "Mensualidad enero 2026",
      "importe": 150.00,
      "fechaPago": "2026-01-05",
      "metodoPago": "transferencia",
      "categoria": "mensualidad",
      "createdAt": "2026-02-01T12:00:00+00:00",
      "updatedAt": "2026-02-01T12:00:00+00:00",
      "deletedAt": null
    }
  ],
  "meta": {
    "total": 1
  }
}
```

### Obtener Ingreso
```http
GET /api/ingresos/{id}
```

**Response 200:**
```json
{
  "id": 1,
  "contratoId": 1,
  "concepto": "Mensualidad enero 2026",
  "importe": 150.00,
  "fechaPago": "2026-01-05",
  "metodoPago": "transferencia",
  "categoria": "mensualidad",
  "createdAt": "2026-02-01T12:00:00+00:00",
  "updatedAt": "2026-02-01T12:00:00+00:00",
  "deletedAt": null
}
```

**Response 404:**
```json
{
  "error": {
    "message": "Ingreso con ID 999 no encontrado",
    "code": "INGRESO_NOT_FOUND"
  }
}
```

### Crear Ingreso
```http
POST /api/ingresos
Content-Type: application/json

{
  "contratoId": 1,
  "concepto": "Mensualidad enero 2026",
  "importe": 150.00,
  "fechaPago": "2026-01-05",
  "metodoPago": "transferencia",
  "categoria": "mensualidad"
}
```

**Response 201:** (mismo formato que GET)

**Response 400 (validación):**
```json
{
  "error": {
    "message": "Validation failed",
    "code": "VALIDATION_ERROR",
    "details": {
      "importe": ["El importe debe ser mayor que 0"],
      "categoria": ["La categoría 'invalida' no es válida"]
    }
  }
}
```

### Actualizar Ingreso
```http
PUT /api/ingresos/{id}
Content-Type: application/json

{
  "contratoId": 1,
  "concepto": "Mensualidad enero 2026 - Corregida",
  "importe": 155.00,
  "fechaPago": "2026-01-05",
  "metodoPago": "transferencia",
  "categoria": "mensualidad"
}
```

**Response 200:** (mismo formato que GET)

**Response 404:** (ingreso no encontrado)

### Eliminar Ingreso
```http
DELETE /api/ingresos/{id}
```

**Response 204:** (sin contenido)

**Response 404:** (ingreso no encontrado)

## Value Objects y Enums

### Importe
Valida importes monetarios:
- Debe ser mayor que 0
- Máximo 2 decimales
- Precisión: 8 dígitos totales, 2 decimales

### IngresoCategoria (Enum)
Categorías de ingresos:
- `mensualidad`: Mensualidad de alquiler
- `fianza`: Depósito de fianza
- `penalizacion`: Penalización por incumplimiento
- `otros`: Otros ingresos

### MetodoPago (Enum)
Métodos de pago disponibles:
- `efectivo`: Pago en efectivo
- `transferencia`: Transferencia bancaria
- `tarjeta`: Pago con tarjeta
- `bizum`: Pago con Bizum

## Métodos del Repositorio

- `save(Ingreso $ingreso)`: Guardar/actualizar
- `remove(Ingreso $ingreso)`: Eliminar
- `findById(IngresoId $id)`: Buscar por ID
- `findAll()`: Listar todos (no eliminados)
- `findByContrato(int $contratoId)`: Filtrar por contrato
- `findByCategoria(IngresoCategoria $categoria)`: Filtrar por categoría
- `findByFechaPagoBetween(\DateTimeImmutable $desde, \DateTimeImmutable $hasta)`: Filtrar por rango de fechas
- `sumByContrato(int $contratoId)`: Sumar total de ingresos por contrato

## Relación con Contrato

Cada ingreso está asociado a un contrato de alquiler:
- Relación Many-to-One con Contrato
- El contrato debe existir al crear el ingreso
- Valida que el contrato no esté eliminado

## Migración

**Archivo:** `migrations/Version20260201000005.php`

Crea la tabla `ingreso` con:
- Campos: id, contrato_id, concepto, importe, fecha_pago, metodo_pago, categoria
- Timestamps: created_at, updated_at, deleted_at
- Auditoría: created_by, updated_by, deleted_by (FK a usuario)
- Foreign Keys: contrato_id (FK a contrato)
- Índices en: contrato_id, categoria, fecha_pago, deleted_at

**Ejecutar migración:**
```bash
php bin/console doctrine:migrations:migrate
```

## Características

1. **Validación de Importe**: Debe ser positivo, máximo 2 decimales
2. **Categorización**: Clasificación de ingresos por tipo
3. **Métodos de pago**: Registro del método utilizado
4. **Historial de pagos**: Fecha de pago registrada
5. **Soft Delete**: Los ingresos no se eliminan físicamente
6. **Auditoría**: Registra quién crea, modifica y elimina
7. **Filtros múltiples**: Por contrato, categoría, rango de fechas
8. **Relación con Contrato**: Validación de existencia del contrato
9. **CQRS**: Separación clara entre comandos y consultas
10. **Eventos de dominio**: IngresoCreated, IngresoUpdated, IngresoDeleted

## Casos de Uso Comunes

### Registrar mensualidad
```json
{
  "contratoId": 1,
  "concepto": "Mensualidad febrero 2026",
  "importe": 150.00,
  "fechaPago": "2026-02-05",
  "metodoPago": "transferencia",
  "categoria": "mensualidad"
}
```

### Registrar fianza
```json
{
  "contratoId": 1,
  "concepto": "Fianza inicial",
  "importe": 300.00,
  "fechaPago": "2026-01-01",
  "metodoPago": "efectivo",
  "categoria": "fianza"
}
```

### Registrar penalización
```json
{
  "contratoId": 1,
  "concepto": "Penalización por retraso",
  "importe": 25.00,
  "fechaPago": "2026-02-10",
  "metodoPago": "tarjeta",
  "categoria": "penalizacion"
}
```
