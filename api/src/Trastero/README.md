# Módulo Trastero

Gestión de trasteros disponibles en locales de almacenamiento, siguiendo arquitectura hexagonal con CQRS.

## Estructura

```
Trastero/
├── Application/          # Capa de Aplicación (Casos de Uso)
│   ├── Command/         # Comandos (Escritura)
│   │   ├── CreateTrastero/
│   │   ├── UpdateTrastero/
│   │   └── DeleteTrastero/
│   ├── Query/           # Consultas (Lectura)
│   │   ├── FindTrastero/
│   │   └── ListTrasteros/
│   └── DTO/             # Data Transfer Objects
│       ├── TrasteroRequest.php
│       └── TrasteroResponse.php
├── Domain/              # Capa de Dominio (Lógica de Negocio)
│   ├── Model/           # Entidades y Value Objects
│   │   ├── Trastero.php
│   │   ├── TrasteroId.php
│   │   ├── Superficie.php         # Value Object con validación
│   │   ├── PrecioMensual.php      # Value Object con validación
│   │   └── TrasteroEstado.php     # Enum
│   ├── Repository/      # Interfaces de Repositorio
│   │   └── TrasteroRepositoryInterface.php
│   ├── Event/           # Eventos de Dominio
│   │   ├── TrasteroCreated.php
│   │   ├── TrasteroUpdated.php
│   │   ├── TrasteroEstadoChanged.php
│   │   └── TrasteroDeleted.php
│   └── Exception/       # Excepciones de Dominio
│       ├── TrasteroNotFoundException.php
│       ├── DuplicateTrasteroException.php
│       ├── InvalidSuperficieException.php
│       ├── InvalidPrecioMensualException.php
│       └── InvalidTrasteroEstadoException.php
└── Infrastructure/      # Capa de Infraestructura
    ├── Controller/
    │   └── TrasteroController.php
    └── Persistence/Doctrine/Repository/
        └── DoctrineTrasteroRepository.php
```

## API REST Endpoints

### Listar Trasteros
```http
GET /api/trasteros
GET /api/trasteros?localId=1
GET /api/trasteros?estado=disponible
GET /api/trasteros?onlyActive=true
```

**Response 200:**
```json
{
  "data": [
    {
      "id": 1,
      "localId": 1,
      "numero": "A-101",
      "nombre": "Trastero pequeño zona A",
      "superficie": 5.50,
      "precioMensual": 75.00,
      "estado": "disponible",
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

### Obtener Trastero
```http
GET /api/trasteros/{id}
```

**Response 200:**
```json
{
  "id": 1,
  "localId": 1,
  "numero": "A-101",
  "nombre": "Trastero pequeño zona A",
  "superficie": 5.50,
  "precioMensual": 75.00,
  "estado": "disponible",
  "createdAt": "2026-02-01T12:00:00+00:00",
  "updatedAt": "2026-02-01T12:00:00+00:00",
  "deletedAt": null
}
```

**Response 404:**
```json
{
  "error": {
    "message": "Trastero con ID 999 no encontrado",
    "code": "TRASTERO_NOT_FOUND"
  }
}
```

### Crear Trastero
```http
POST /api/trasteros
Content-Type: application/json

{
  "localId": 1,
  "numero": "A-101",
  "nombre": "Trastero pequeño zona A",
  "superficie": 5.50,
  "precioMensual": 75.00,
  "estado": "disponible"
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
      "superficie": ["La superficie debe ser mayor que 0"],
      "precioMensual": ["El precio mensual debe ser mayor que 0"]
    }
  }
}
```

**Response 409 (duplicado):**
```json
{
  "error": {
    "message": "Ya existe un trastero con número 'A-101' en el local 1",
    "code": "DUPLICATE_TRASTERO"
  }
}
```

### Actualizar Trastero
```http
PUT /api/trasteros/{id}
Content-Type: application/json

{
  "localId": 1,
  "numero": "A-101",
  "nombre": "Trastero pequeño zona A - Reformado",
  "superficie": 6.00,
  "precioMensual": 80.00,
  "estado": "disponible"
}
```

**Response 200:** (mismo formato que GET)

**Response 404:** (trastero no encontrado)

### Eliminar Trastero
```http
DELETE /api/trasteros/{id}
```

**Response 204:** (sin contenido)

**Response 404:** (trastero no encontrado)

## Value Objects y Enums

### Superficie
Valida superficie en metros cuadrados:
- Debe ser mayor que 0
- Máximo 2 decimales
- Precisión: 6 dígitos totales, 2 decimales
- Rango típico: 1.00 a 100.00 m²

### PrecioMensual
Valida precio de alquiler mensual:
- Debe ser mayor que 0
- Máximo 2 decimales
- Precisión: 8 dígitos totales, 2 decimales

### TrasteroEstado (Enum)
Estados posibles de un trastero:
- `disponible`: Disponible para alquilar
- `ocupado`: Actualmente alquilado
- `mantenimiento`: En mantenimiento o reparación
- `reservado`: Reservado para un cliente

## Métodos del Repositorio

- `save(Trastero $trastero)`: Guardar/actualizar
- `remove(Trastero $trastero)`: Eliminar
- `findById(TrasteroId $id)`: Buscar por ID
- `findAll()`: Listar todos (no eliminados)
- `findByLocal(int $localId)`: Filtrar por local
- `findByEstado(TrasteroEstado $estado)`: Filtrar por estado
- `findDisponibles()`: Listar trasteros disponibles
- `findByLocalAndNumero(int $localId, string $numero)`: Buscar por local y número
- `existsByLocalAndNumero(int $localId, string $numero, ?int $excludeId)`: Verificar duplicado

## Relación con Local

Cada trastero pertenece a un local:
- Relación Many-to-One con Local
- El local debe existir al crear el trastero
- Combinación localId + numero debe ser única (constraint de BD)

## Método de Dominio

### changeEstado()
Método específico para cambiar solo el estado del trastero:

```php
$trastero->changeEstado(TrasteroEstado::OCUPADO);
```

Este método:
- Actualiza solo el estado
- Actualiza automáticamente `updatedAt`
- Dispara evento `TrasteroEstadoChanged`

## Migración

**Archivo:** `migrations/Version20260201000007.php`

Crea la tabla `trastero` con:
- Campos: id, local_id, numero, nombre, superficie, precio_mensual, estado
- Timestamps: created_at, updated_at, deleted_at
- Auditoría: created_by, updated_by, deleted_by (FK a usuario)
- Foreign Keys: local_id (FK a local)
- Unique Constraint: (local_id, numero) - No puede haber duplicados
- Índices en: local_id, estado, deleted_at

**Ejecutar migración:**
```bash
php bin/console doctrine:migrations:migrate
```

## Características

1. **Validación de Superficie**: Debe ser positiva, máximo 2 decimales
2. **Validación de Precio**: Debe ser positivo, máximo 2 decimales
3. **Estados del trastero**: Disponible, ocupado, mantenimiento, reservado
4. **Unicidad**: No puede haber dos trasteros con el mismo número en un local
5. **Soft Delete**: Los trasteros no se eliminan físicamente
6. **Auditoría**: Registra quién crea, modifica y elimina
7. **Filtros múltiples**: Por local, estado
8. **Relación con Local**: Validación de existencia del local
9. **CQRS**: Separación clara entre comandos y consultas
10. **Eventos de dominio**: TrasteroCreated, TrasteroUpdated, TrasteroEstadoChanged, TrasteroDeleted

## Casos de Uso Comunes

### Crear trastero pequeño
```json
{
  "localId": 1,
  "numero": "A-101",
  "nombre": "Trastero pequeño",
  "superficie": 3.50,
  "precioMensual": 50.00,
  "estado": "disponible"
}
```

### Crear trastero mediano
```json
{
  "localId": 1,
  "numero": "B-201",
  "nombre": "Trastero mediano",
  "superficie": 8.00,
  "precioMensual": 100.00,
  "estado": "disponible"
}
```

### Crear trastero grande
```json
{
  "localId": 1,
  "numero": "C-301",
  "nombre": "Trastero grande",
  "superficie": 15.00,
  "precioMensual": 180.00,
  "estado": "disponible"
}
```

### Marcar trastero como ocupado
```json
{
  "localId": 1,
  "numero": "A-101",
  "nombre": "Trastero pequeño",
  "superficie": 3.50,
  "precioMensual": 50.00,
  "estado": "ocupado"
}
```

## Convención de Numeración

Se recomienda usar un sistema de numeración que incluya:
- Zona o planta (A, B, C...)
- Número secuencial (001, 002, 003...)

Ejemplos:
- `A-101`, `A-102`, `A-103` (Zona A)
- `B-201`, `B-202`, `B-203` (Zona B)
- `P1-001`, `P1-002` (Planta 1)
- `S1-001`, `S1-002` (Sótano 1)
