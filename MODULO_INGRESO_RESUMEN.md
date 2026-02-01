# Módulo Ingreso - Resumen de Implementación

## Estructura del Módulo

El módulo de Ingreso ha sido implementado siguiendo la arquitectura hexagonal del proyecto, con separación clara de responsabilidades en las capas Domain, Application e Infrastructure.

### Domain Layer (D:\Code\trasteros\api\src\Ingreso\Domain)

#### Models
- **Ingreso.php**: Entidad principal con todos los campos del modelo de datos
- **IngresoId.php**: Value Object para el ID del ingreso
- **Importe.php**: Value Object para el importe (validación: 0-99999.99)
- **IngresoCategoria.php**: Enum (mensualidad, fianza, penalizacion, otros)
- **MetodoPago.php**: Enum (efectivo, transferencia, tarjeta, bizum)

#### Repository
- **IngresoRepositoryInterface.php**: Interfaz con métodos para:
  - CRUD básico (save, remove, findById, findAll)
  - Búsquedas filtradas (por contrato, categoría, rango de fechas)
  - Consultas de agregación (totales por contrato, categoría, etc.)

#### Exceptions
- **IngresoNotFoundException.php**: Cuando no se encuentra un ingreso
- **InvalidImporteException.php**: Validación de importe (negativo o muy grande)
- **InvalidIngresoCategoriaException.php**: Categoría inválida
- **InvalidMetodoPagoException.php**: Método de pago inválido

#### Events
- **IngresoCreated.php**: Evento de ingreso creado
- **IngresoUpdated.php**: Evento de ingreso actualizado
- **IngresoDeleted.php**: Evento de ingreso eliminado

### Application Layer (D:\Code\trasteros\api\src\Ingreso\Application)

#### DTOs
- **IngresoRequest.php**: DTO para requests con validaciones Symfony
- **IngresoResponse.php**: DTO para responses con información completa del contrato

#### Commands (CQRS - Write Operations)
- **CreateIngreso**: Crear nuevo ingreso
- **UpdateIngreso**: Actualizar ingreso existente
- **DeleteIngreso**: Eliminar ingreso (hard delete)

#### Queries (CQRS - Read Operations)
- **FindIngreso**: Buscar ingreso por ID
- **ListIngresos**: Listar ingresos con filtros opcionales:
  - contratoId
  - categoria
  - desde/hasta (rango de fechas)
  - onlyActive (solo no eliminados)

### Infrastructure Layer (D:\Code\trasteros\api\src\Ingreso\Infrastructure)

#### Persistence
- **DoctrineIngresoRepository.php**: Implementación del repositorio usando Doctrine ORM
  - Consultas optimizadas con QueryBuilder
  - Soporte para soft delete (filtrado por deletedAt)
  - Métodos de agregación para totales

#### Controller
- **IngresoController.php**: API REST con endpoints:
  - `GET /api/ingresos` - Listar ingresos
  - `GET /api/ingresos/{id}` - Obtener ingreso
  - `POST /api/ingresos` - Crear ingreso
  - `PUT /api/ingresos/{id}` - Actualizar ingreso
  - `DELETE /api/ingresos/{id}` - Eliminar ingreso

## Database Migration

**Version20260201000008.php** - Crea la tabla `ingreso` con:
- Campos: id, contrato_id, concepto, importe, fecha_pago, metodo_pago, categoria
- Auditoría: created_at, created_by, updated_at, updated_by, deleted_at, deleted_by
- Foreign Keys: contrato, usuario (para auditoría)
- Índices: contrato_id, fecha_pago, categoria, metodo_pago, deleted_at

## Services Configuration

Registrado en `config/services.yaml`:
```yaml
App\Ingreso\Domain\Repository\IngresoRepositoryInterface:
    class: App\Ingreso\Infrastructure\Persistence\Doctrine\Repository\DoctrineIngresoRepository
```

## Características Implementadas

1. **CRUD Completo**: Crear, leer, actualizar y eliminar ingresos
2. **Soft Delete**: Los registros no se eliminan físicamente, se marca deleted_at
3. **Auditoría**: Seguimiento de quién creó, actualizó y eliminó cada registro
4. **Validaciones**:
   - Importe entre 0 y 99999.99
   - Fecha en formato Y-m-d
   - Categoría y método de pago validados contra enums
5. **Búsquedas Avanzadas**:
   - Por contrato
   - Por categoría
   - Por rango de fechas
   - Combinaciones de filtros
6. **Consultas de Agregación**: Totales de importes por diferentes criterios
7. **API REST**: Endpoints completos con manejo de errores apropiado
8. **CQRS**: Separación clara entre comandos (escritura) y queries (lectura)

## Relaciones

- **Contrato**: Un ingreso pertenece a un contrato (ManyToOne)
- **User**: Relaciones opcionales para auditoría (created_by, updated_by, deleted_by)

## Próximos Pasos

1. Ejecutar la migración: `php bin/console doctrine:migrations:migrate`
2. Limpiar caché: `php bin/console cache:clear`
3. Probar los endpoints API con herramientas como Postman o cURL
4. (Opcional) Implementar tests unitarios y de integración

## Ejemplo de Uso de la API

### Crear Ingreso
```bash
POST /api/ingresos
{
  "contratoId": 1,
  "concepto": "Pago mensualidad enero 2026",
  "importe": 150.00,
  "fechaPago": "2026-01-15",
  "categoria": "mensualidad",
  "metodoPago": "transferencia"
}
```

### Listar Ingresos de un Contrato
```bash
GET /api/ingresos?contratoId=1&onlyActive=true
```

### Obtener Total de Mensualidades de un Contrato
```bash
GET /api/ingresos?contratoId=1&categoria=mensualidad
```
