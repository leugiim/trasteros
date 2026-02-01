# Ejemplos de Uso de la API de Contratos

## Crear un contrato

```bash
curl -X POST http://localhost:8000/api/contratos \
  -H "Content-Type: application/json" \
  -d '{
    "trasteroId": 1,
    "clienteId": 1,
    "fechaInicio": "2024-01-01",
    "fechaFin": "2024-12-31",
    "precioMensual": 150.00,
    "fianza": 300.00,
    "fianzaPagada": false,
    "estado": "activo"
  }'
```

**Respuesta exitosa (201):**
```json
{
  "id": 1,
  "trastero": {
    "id": 1,
    "numero": "A-001"
  },
  "cliente": {
    "id": 1,
    "nombre": "Juan Pérez García"
  },
  "fechaInicio": "2024-01-01",
  "fechaFin": "2024-12-31",
  "precioMensual": 150.00,
  "fianza": 300.00,
  "fianzaPagada": false,
  "estado": "activo",
  "duracionMeses": 12,
  "createdAt": "2024-01-01 10:00:00",
  "updatedAt": "2024-01-01 10:00:00"
}
```

**Error: Trastero ya alquilado (409):**
```json
{
  "error": {
    "message": "El trastero con id 1 ya tiene un contrato activo",
    "code": "TRASTERO_ALREADY_RENTED"
  }
}
```

**Error: Validación de fechas (400):**
```json
{
  "error": {
    "message": "Validation failed",
    "code": "VALIDATION_ERROR",
    "details": {
      "fechaFin": ["La fecha de fin debe ser posterior a la fecha de inicio"]
    }
  }
}
```

## Listar todos los contratos

```bash
curl http://localhost:8000/api/contratos
```

**Respuesta (200):**
```json
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
  "meta": {
    "total": 1
  }
}
```

## Filtrar contratos por estado

```bash
# Solo contratos activos
curl "http://localhost:8000/api/contratos?estado=activo"

# Solo contratos finalizados
curl "http://localhost:8000/api/contratos?estado=finalizado"

# Solo contratos cancelados
curl "http://localhost:8000/api/contratos?estado=cancelado"
```

## Obtener un contrato específico

```bash
curl http://localhost:8000/api/contratos/1
```

**Respuesta (200):**
```json
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
```

**Error: No encontrado (404):**
```json
{
  "error": {
    "message": "Contrato with id 999 not found",
    "code": "CONTRATO_NOT_FOUND"
  }
}
```

## Actualizar un contrato

```bash
curl -X PUT http://localhost:8000/api/contratos/1 \
  -H "Content-Type: application/json" \
  -d '{
    "trasteroId": 1,
    "clienteId": 1,
    "fechaInicio": "2024-01-01",
    "fechaFin": "2025-01-01",
    "precioMensual": 175.00,
    "fianza": 350.00,
    "fianzaPagada": true,
    "estado": "activo"
  }'
```

## Contratos por trastero

```bash
# Todos los contratos del trastero
curl http://localhost:8000/api/contratos/trastero/1

# Solo contratos activos del trastero
curl "http://localhost:8000/api/contratos/trastero/1?onlyActivos=true"
```

## Contratos por cliente

```bash
# Todos los contratos del cliente
curl http://localhost:8000/api/contratos/cliente/1

# Solo contratos activos del cliente
curl "http://localhost:8000/api/contratos/cliente/1?onlyActivos=true"
```

## Marcar fianza como pagada

```bash
curl -X PATCH http://localhost:8000/api/contratos/1/marcar-fianza-pagada
```

**Respuesta (200):**
```json
{
  "id": 1,
  "trastero": { "id": 1, "numero": "A-001" },
  "cliente": { "id": 1, "nombre": "Juan Pérez García" },
  "fechaInicio": "2024-01-01",
  "fechaFin": "2024-12-31",
  "precioMensual": 150.00,
  "fianza": 300.00,
  "fianzaPagada": true,  // <-- Cambiado a true
  "estado": "activo",
  "duracionMeses": 12,
  "createdAt": "2024-01-01 10:00:00",
  "updatedAt": "2024-02-01 14:30:00"
}
```

## Finalizar un contrato

```bash
curl -X PATCH http://localhost:8000/api/contratos/1/finalizar
```

**Respuesta (200):**
```json
{
  "id": 1,
  "trastero": { "id": 1, "numero": "A-001" },
  "cliente": { "id": 1, "nombre": "Juan Pérez García" },
  "fechaInicio": "2024-01-01",
  "fechaFin": "2024-02-01",  // <-- Establecida a fecha actual si no existía
  "precioMensual": 150.00,
  "fianza": 300.00,
  "fianzaPagada": true,
  "estado": "finalizado",    // <-- Cambiado a finalizado
  "duracionMeses": 1,
  "createdAt": "2024-01-01 10:00:00",
  "updatedAt": "2024-02-01 14:30:00"
}
```

## Cancelar un contrato

```bash
curl -X PATCH http://localhost:8000/api/contratos/1/cancelar
```

**Respuesta (200):**
```json
{
  "id": 1,
  "trastero": { "id": 1, "numero": "A-001" },
  "cliente": { "id": 1, "nombre": "Juan Pérez García" },
  "fechaInicio": "2024-01-01",
  "fechaFin": "2024-12-31",
  "precioMensual": 150.00,
  "fianza": 300.00,
  "fianzaPagada": true,
  "estado": "cancelado",     // <-- Cambiado a cancelado
  "duracionMeses": 12,
  "createdAt": "2024-01-01 10:00:00",
  "updatedAt": "2024-02-01 14:30:00"
}
```

## Eliminar un contrato (soft delete)

```bash
curl -X DELETE http://localhost:8000/api/contratos/1
```

**Respuesta (204):**
```
(Sin contenido)
```

## Casos de Uso Completos

### Escenario 1: Crear contrato nuevo para trastero disponible

```bash
# 1. Verificar que el trastero esté disponible
curl http://localhost:8000/api/trasteros/1

# 2. Crear el contrato
curl -X POST http://localhost:8000/api/contratos \
  -H "Content-Type: application/json" \
  -d '{
    "trasteroId": 1,
    "clienteId": 5,
    "fechaInicio": "2024-02-01",
    "precioMensual": 120.00,
    "fianza": 240.00,
    "fianzaPagada": false,
    "estado": "pendiente"
  }'

# 3. Una vez confirmado y pagada la fianza, marcarla
curl -X PATCH http://localhost:8000/api/contratos/2/marcar-fianza-pagada

# 4. Actualizar a activo
curl -X PUT http://localhost:8000/api/contratos/2 \
  -H "Content-Type: application/json" \
  -d '{
    "trasteroId": 1,
    "clienteId": 5,
    "fechaInicio": "2024-02-01",
    "precioMensual": 120.00,
    "fianza": 240.00,
    "fianzaPagada": true,
    "estado": "activo"
  }'
```

### Escenario 2: Ver historial de contratos de un cliente

```bash
# Ver todos los contratos del cliente
curl http://localhost:8000/api/contratos/cliente/5

# Ver solo contratos activos del cliente
curl "http://localhost:8000/api/contratos/cliente/5?onlyActivos=true"
```

### Escenario 3: Finalizar contrato cuando cliente deja el trastero

```bash
# 1. Finalizar el contrato
curl -X PATCH http://localhost:8000/api/contratos/2/finalizar

# 2. Verificar que el trastero ya no tiene contratos activos
curl "http://localhost:8000/api/contratos/trastero/1?onlyActivos=true"
```

### Escenario 4: Intentar crear contrato duplicado (debe fallar)

```bash
# Crear primer contrato (debe funcionar)
curl -X POST http://localhost:8000/api/contratos \
  -H "Content-Type: application/json" \
  -d '{
    "trasteroId": 3,
    "clienteId": 1,
    "fechaInicio": "2024-02-01",
    "precioMensual": 100.00,
    "estado": "activo"
  }'

# Intentar crear segundo contrato para el mismo trastero (debe fallar con 409)
curl -X POST http://localhost:8000/api/contratos \
  -H "Content-Type: application/json" \
  -d '{
    "trasteroId": 3,
    "clienteId": 2,
    "fechaInicio": "2024-02-01",
    "precioMensual": 100.00,
    "estado": "activo"
  }'

# Respuesta esperada (409):
# {
#   "error": {
#     "message": "El trastero con id 3 ya tiene un contrato activo",
#     "code": "TRASTERO_ALREADY_RENTED"
#   }
# }
```
