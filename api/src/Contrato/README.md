# Módulo Contrato

Módulo completo de Contratos siguiendo arquitectura hexagonal con CQRS.

## Estructura

```
Contrato/
├── Domain/
│   ├── Model/
│   │   ├── Contrato.php              # Entidad principal con lógica de negocio
│   │   ├── ContratoEstado.php        # Enum: activo, finalizado, cancelado, pendiente
│   │   ├── ContratoId.php            # Value Object
│   │   ├── PrecioMensual.php         # Value Object con validación
│   │   └── Fianza.php                # Value Object con validación
│   ├── Repository/
│   │   └── ContratoRepositoryInterface.php
│   ├── Event/
│   │   ├── ContratoCreated.php       # Evento de creación
│   │   ├── ContratoFinalizado.php    # Evento de finalización
│   │   └── ContratoCancelado.php     # Evento de cancelación
│   └── Exception/
│       ├── ContratoNotFoundException.php
│       ├── InvalidPrecioMensualException.php
│       ├── InvalidFianzaException.php
│       ├── InvalidContratoDateException.php
│       └── TrasteroAlreadyRentedException.php
├── Application/
│   ├── Command/
│   │   ├── CreateContrato/
│   │   ├── UpdateContrato/
│   │   ├── DeleteContrato/
│   │   ├── FinalizarContrato/
│   │   ├── CancelarContrato/
│   │   └── MarcarFianzaPagada/
│   ├── Query/
│   │   ├── FindContrato/
│   │   ├── ListContratos/
│   │   ├── FindContratosByTrastero/
│   │   ├── FindContratosByCliente/
│   │   └── VerificarContratoActivoTrastero/
│   └── DTO/
│       ├── ContratoRequest.php
│       └── ContratoResponse.php
└── Infrastructure/
    ├── Persistence/Doctrine/Repository/
    │   └── DoctrineContratoRepository.php
    └── Controller/
        └── ContratoController.php
```

## Endpoints API REST

### CRUD Básico

#### Listar contratos
```
GET /api/contratos
Query params:
  - estado: activo|finalizado|cancelado|pendiente (opcional)

Response 200:
{
  "data": [
    {
      "id": 1,
      "trastero": { "id": 1, "numero": "A-001" },
      "cliente": { "id": 1, "nombre": "Juan Pérez García" },
      "fechaInicio": "2024-01-01",
      "fechaFin": "2024-12-31",
      "precioMensual": 150.00,
      "fianza": 300.00,
      "fianzaPagada": true,
      "estado": "activo",
      "duracionMeses": 12,
      "createdAt": "2024-01-01 10:00:00",
      "updatedAt": "2024-01-01 10:00:00"
    }
  ],
  "meta": { "total": 1 }
}
```

#### Obtener contrato por ID
```
GET /api/contratos/{id}

Response 200: (mismo formato que item de listado)
Response 404: { "error": { "message": "...", "code": "CONTRATO_NOT_FOUND" } }
```

#### Crear contrato
```
POST /api/contratos
Body:
{
  "trasteroId": 1,
  "clienteId": 1,
  "fechaInicio": "2024-01-01",
  "fechaFin": "2024-12-31",          // Opcional
  "precioMensual": 150.00,
  "fianza": 300.00,                  // Opcional
  "fianzaPagada": false,             // Opcional, default: false
  "estado": "activo"                 // Opcional, default: activo
}

Response 201: (contrato creado)
Response 400: (errores de validación)
Response 404: (trastero o cliente no encontrado)
Response 409: { "error": { "message": "...", "code": "TRASTERO_ALREADY_RENTED" } }
```

#### Actualizar contrato
```
PUT /api/contratos/{id}
Body: (mismo que crear)

Response 200: (contrato actualizado)
Response 400: (errores de validación)
Response 404: (contrato, trastero o cliente no encontrado)
Response 409: (trastero ya alquilado si se cambia de trastero)
```

#### Eliminar contrato (soft delete)
```
DELETE /api/contratos/{id}

Response 204: (sin contenido)
Response 404: (contrato no encontrado)
```

### Endpoints Específicos de Negocio

#### Finalizar contrato
```
PATCH /api/contratos/{id}/finalizar

- Cambia estado a "finalizado"
- Si no tiene fecha_fin, la establece a la fecha actual

Response 200: (contrato finalizado)
Response 404: (contrato no encontrado)
```

#### Cancelar contrato
```
PATCH /api/contratos/{id}/cancelar

- Cambia estado a "cancelado"

Response 200: (contrato cancelado)
Response 404: (contrato no encontrado)
```

#### Marcar fianza como pagada
```
PATCH /api/contratos/{id}/marcar-fianza-pagada

- Marca fianzaPagada = true

Response 200: (contrato actualizado)
Response 404: (contrato no encontrado)
```

### Consultas por Relación

#### Contratos por trastero
```
GET /api/contratos/trastero/{trasteroId}
Query params:
  - onlyActivos: true|false (opcional, default: false)

Response 200: (lista de contratos del trastero)
```

#### Contratos por cliente
```
GET /api/contratos/cliente/{clienteId}
Query params:
  - onlyActivos: true|false (opcional, default: false)

Response 200: (lista de contratos del cliente)
```

## Validaciones de Negocio

### En Creación/Actualización:

1. **No permitir crear contrato si trastero ya tiene contrato activo**
   - Exception: `TrasteroAlreadyRentedException`
   - HTTP 409 Conflict

2. **fecha_fin debe ser posterior a fecha_inicio**
   - Exception: `InvalidContratoDateException`
   - HTTP 400 Bad Request

3. **precioMensual debe ser positivo y < 999999.99**
   - Exception: `InvalidPrecioMensualException`
   - HTTP 400 Bad Request

4. **fianza debe ser positiva y < 999999.99**
   - Exception: `InvalidFianzaException`
   - HTTP 400 Bad Request

## Métodos Útiles de la Entidad

### Contrato

```php
// Verificaciones de estado
$contrato->isActivo(): bool

// Cálculos
$contrato->getDuracionMeses(): ?int  // Devuelve meses entre inicio y fin (null si no hay fin)

// Cambios de estado
$contrato->marcarFianzaPagada(): void
$contrato->finalizar(): void          // Cambia a finalizado y establece fecha_fin si no existe
$contrato->cancelar(): void           // Cambia a cancelado

// Soft delete
$contrato->softDelete(User $user): void
$contrato->restore(): void
```

## Métodos del Repositorio

```php
// Básicos
$repository->save(Contrato $contrato): void
$repository->findById(int $id): ?Contrato
$repository->findAll(): array

// Por relaciones
$repository->findByTrasteroId(int $trasteroId): array
$repository->findByClienteId(int $clienteId): array

// Por estado
$repository->findByEstado(ContratoEstado $estado): array
$repository->findContratosActivosByCliente(int $clienteId): array
$repository->findContratosActivosByTrastero(int $trasteroId): array

// Verificaciones
$repository->hasContratoActivoTrastero(int $trasteroId): bool
$repository->findOneContratoActivoByTrastero(int $trasteroId): ?Contrato
```

## Value Objects

### PrecioMensual
- Valida que sea >= 0 y <= 999999.99
- Inmutable

### Fianza
- Valida que sea >= 0 y <= 999999.99
- Inmutable

## Eventos de Dominio

### ContratoCreated
```php
new ContratoCreated(
    contratoId: int,
    trasteroId: int,
    clienteId: int
)
```

### ContratoFinalizado
```php
new ContratoFinalizado(
    contratoId: int,
    trasteroId: int
)
```

### ContratoCancelado
```php
new ContratoCancelado(
    contratoId: int,
    trasteroId: int
)
```

## Migración de Base de Datos

**Archivo:** `migrations/Version20260201000007.php`

Ejecutar con:
```bash
php bin/console doctrine:migrations:migrate
```

Crea tabla `contrato` con:
- Columnas según modelo SQL proporcionado
- Foreign keys a trastero, cliente, usuario
- Índices optimizados para búsquedas frecuentes
- Soft delete (deleted_at, deleted_by)
- Auditoría (created_at, created_by, updated_at, updated_by)

## Configuración de Servicios

El repositorio está registrado automáticamente en `config/services.yaml`:

```yaml
App\Contrato\Domain\Repository\ContratoRepositoryInterface:
    class: App\Contrato\Infrastructure\Persistence\Doctrine\Repository\DoctrineContratoRepository
```

## Convenciones Seguidas

- Arquitectura Hexagonal
- CQRS (Commands y Queries separados)
- Value Objects para validaciones de dominio
- PHP Enums para estados
- Tipado estricto y readonly properties
- Named constructors
- API REST estándar (verbos HTTP correctos, códigos de estado apropiados)
- Respuestas JSON consistentes
- Soft delete
- Auditoría completa
