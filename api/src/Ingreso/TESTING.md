# Testing - Módulo Ingreso

Ejemplos de pruebas de la API del módulo Ingreso.

## Prerrequisitos

1. Servidor en ejecución:
```bash
cd api
symfony server:start
# o
php -S localhost:8000 -t public/
```

2. Base de datos migrada:
```bash
php bin/console doctrine:migrations:migrate
```

3. Tener al menos un contrato creado (necesario para crear ingresos)

## Pruebas con cURL

### 1. Crear Ingreso

#### Crear ingreso de mensualidad
```bash
curl -X POST http://localhost:8000/api/ingresos \
  -H "Content-Type: application/json" \
  -d '{
    "contratoId": 1,
    "concepto": "Mensualidad enero 2026",
    "importe": 150.00,
    "fechaPago": "2026-01-05",
    "metodoPago": "transferencia",
    "categoria": "mensualidad"
  }'
```

**Respuesta esperada (201 Created)**:
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

#### Crear ingreso de fianza
```bash
curl -X POST http://localhost:8000/api/ingresos \
  -H "Content-Type: application/json" \
  -d '{
    "contratoId": 1,
    "concepto": "Fianza inicial",
    "importe": 300.00,
    "fechaPago": "2026-01-01",
    "metodoPago": "efectivo",
    "categoria": "fianza"
  }'
```

#### Crear ingreso sin método de pago (opcional)
```bash
curl -X POST http://localhost:8000/api/ingresos \
  -H "Content-Type: application/json" \
  -d '{
    "contratoId": 1,
    "concepto": "Mensualidad febrero 2026",
    "importe": 150.00,
    "fechaPago": "2026-02-05",
    "categoria": "mensualidad"
  }'
```

#### Crear ingreso con Bizum
```bash
curl -X POST http://localhost:8000/api/ingresos \
  -H "Content-Type: application/json" \
  -d '{
    "contratoId": 1,
    "concepto": "Mensualidad marzo 2026",
    "importe": 150.00,
    "fechaPago": "2026-03-05",
    "metodoPago": "bizum",
    "categoria": "mensualidad"
  }'
```

#### Crear ingreso de penalización
```bash
curl -X POST http://localhost:8000/api/ingresos \
  -H "Content-Type: application/json" \
  -d '{
    "contratoId": 1,
    "concepto": "Penalización por retraso en pago",
    "importe": 25.00,
    "fechaPago": "2026-02-10",
    "metodoPago": "tarjeta",
    "categoria": "penalizacion"
  }'
```

---

### 2. Listar Ingresos

#### Listar todos los ingresos
```bash
curl http://localhost:8000/api/ingresos
```

**Respuesta esperada (200 OK)**:
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

#### Filtrar por contrato
```bash
curl "http://localhost:8000/api/ingresos?contratoId=1"
```

#### Filtrar por categoría
```bash
curl "http://localhost:8000/api/ingresos?categoria=mensualidad"
curl "http://localhost:8000/api/ingresos?categoria=fianza"
curl "http://localhost:8000/api/ingresos?categoria=penalizacion"
```

#### Filtrar por rango de fechas
```bash
curl "http://localhost:8000/api/ingresos?desde=2026-01-01&hasta=2026-12-31"
```

#### Filtrar solo activos (no eliminados)
```bash
curl "http://localhost:8000/api/ingresos?onlyActive=true"
```

#### Combinar filtros
```bash
curl "http://localhost:8000/api/ingresos?contratoId=1&categoria=mensualidad&desde=2026-01-01&hasta=2026-06-30"
```

---

### 3. Obtener Ingreso por ID

```bash
curl http://localhost:8000/api/ingresos/1
```

**Respuesta esperada (200 OK)**:
```json
{
  "id": 1,
  "contratoId": 1,
  "concepto": "Mensualidad enero 2026",
  "importe": 150.00,
  ...
}
```

**Ingreso no encontrado (404 Not Found)**:
```bash
curl http://localhost:8000/api/ingresos/999
```

```json
{
  "error": {
    "message": "Ingreso con ID 999 no encontrado",
    "code": "INGRESO_NOT_FOUND"
  }
}
```

---

### 4. Actualizar Ingreso

```bash
curl -X PUT http://localhost:8000/api/ingresos/1 \
  -H "Content-Type: application/json" \
  -d '{
    "contratoId": 1,
    "concepto": "Mensualidad enero 2026 - Corregida",
    "importe": 155.00,
    "fechaPago": "2026-01-05",
    "metodoPago": "transferencia",
    "categoria": "mensualidad"
  }'
```

**Respuesta esperada (200 OK)**: Ingreso actualizado

#### Cambiar método de pago
```bash
curl -X PUT http://localhost:8000/api/ingresos/1 \
  -H "Content-Type: application/json" \
  -d '{
    "contratoId": 1,
    "concepto": "Mensualidad enero 2026",
    "importe": 150.00,
    "fechaPago": "2026-01-05",
    "metodoPago": "efectivo",
    "categoria": "mensualidad"
  }'
```

---

### 5. Eliminar Ingreso

```bash
curl -X DELETE http://localhost:8000/api/ingresos/1
```

**Respuesta esperada (204 No Content)**: Sin cuerpo de respuesta

---

## Casos de Error y Validación

### 1. Contrato No Existe

```bash
curl -X POST http://localhost:8000/api/ingresos \
  -H "Content-Type: application/json" \
  -d '{
    "contratoId": 999,
    "concepto": "Test",
    "importe": 100.00,
    "fechaPago": "2026-01-05",
    "categoria": "mensualidad"
  }'
```

**Respuesta esperada (400 Bad Request)**:
```json
{
  "error": {
    "message": "Validation failed",
    "code": "VALIDATION_ERROR",
    "details": {
      "contratoId": ["Contrato con ID 999 no encontrado"]
    }
  }
}
```

---

### 2. Importe Inválido

#### Importe negativo
```bash
curl -X POST http://localhost:8000/api/ingresos \
  -H "Content-Type: application/json" \
  -d '{
    "contratoId": 1,
    "concepto": "Test",
    "importe": -50.00,
    "fechaPago": "2026-01-05",
    "categoria": "mensualidad"
  }'
```

**Respuesta esperada (400 Bad Request)**:
```json
{
  "error": {
    "message": "Validation failed",
    "code": "VALIDATION_ERROR",
    "details": {
      "importe": ["El importe debe ser mayor que 0"]
    }
  }
}
```

#### Importe cero
```bash
curl -X POST http://localhost:8000/api/ingresos \
  -H "Content-Type: application/json" \
  -d '{
    "contratoId": 1,
    "concepto": "Test",
    "importe": 0.00,
    "fechaPago": "2026-01-05",
    "categoria": "mensualidad"
  }'
```

**Respuesta esperada (400 Bad Request)**:
```json
{
  "error": {
    "message": "Validation failed",
    "code": "VALIDATION_ERROR",
    "details": {
      "importe": ["El importe debe ser mayor que 0"]
    }
  }
}
```

---

### 3. Categoría Inválida

```bash
curl -X POST http://localhost:8000/api/ingresos \
  -H "Content-Type: application/json" \
  -d '{
    "contratoId": 1,
    "concepto": "Test",
    "importe": 100.00,
    "fechaPago": "2026-01-05",
    "categoria": "invalida"
  }'
```

**Respuesta esperada (400 Bad Request)**:
```json
{
  "error": {
    "message": "Validation failed",
    "code": "VALIDATION_ERROR",
    "details": {
      "categoria": ["La categoría 'invalida' no es válida. Valores permitidos: mensualidad, fianza, penalizacion, otros"]
    }
  }
}
```

---

### 4. Método de Pago Inválido

```bash
curl -X POST http://localhost:8000/api/ingresos \
  -H "Content-Type: application/json" \
  -d '{
    "contratoId": 1,
    "concepto": "Test",
    "importe": 100.00,
    "fechaPago": "2026-01-05",
    "metodoPago": "criptomoneda",
    "categoria": "mensualidad"
  }'
```

**Respuesta esperada (400 Bad Request)**:
```json
{
  "error": {
    "message": "Validation failed",
    "code": "VALIDATION_ERROR",
    "details": {
      "metodoPago": ["El método de pago 'criptomoneda' no es válido. Valores permitidos: efectivo, transferencia, tarjeta, bizum"]
    }
  }
}
```

---

### 5. Fecha de Pago Inválida

```bash
curl -X POST http://localhost:8000/api/ingresos \
  -H "Content-Type: application/json" \
  -d '{
    "contratoId": 1,
    "concepto": "Test",
    "importe": 100.00,
    "fechaPago": "fecha-invalida",
    "categoria": "mensualidad"
  }'
```

**Respuesta esperada (400 Bad Request)**:
```json
{
  "error": {
    "message": "Validation failed",
    "code": "VALIDATION_ERROR",
    "details": {
      "fechaPago": ["La fecha debe tener el formato Y-m-d (ejemplo: 2026-01-05)"]
    }
  }
}
```

---

### 6. Campos Obligatorios Faltantes

```bash
curl -X POST http://localhost:8000/api/ingresos \
  -H "Content-Type: application/json" \
  -d '{
    "contratoId": 1
  }'
```

**Respuesta esperada (400 Bad Request)**:
```json
{
  "error": {
    "message": "Validation failed",
    "code": "VALIDATION_ERROR",
    "details": {
      "concepto": ["El concepto es obligatorio"],
      "importe": ["El importe es obligatorio"],
      "fechaPago": ["La fecha de pago es obligatoria"],
      "categoria": ["La categoría es obligatoria"]
    }
  }
}
```

---

## Casos de Prueba Válidos

### Categorías Válidas
```bash
mensualidad
fianza
penalizacion
otros
```

### Métodos de Pago Válidos
```bash
efectivo
transferencia
tarjeta
bizum
```

### Importes Válidos
```bash
0.01      # Mínimo válido
150.00    # Mensualidad típica
300.00    # Fianza
25.50     # Penalización
9999.99   # Importe alto
```

### Fechas Válidas
```bash
2026-01-05
2026-02-01
2026-12-31
```

---

## Script de Prueba Completo

```bash
#!/bin/bash

BASE_URL="http://localhost:8000/api/ingresos"

echo "=== 1. Crear ingreso de mensualidad ==="
RESPONSE=$(curl -s -X POST "$BASE_URL" \
  -H "Content-Type: application/json" \
  -d '{
    "contratoId": 1,
    "concepto": "Mensualidad enero 2026",
    "importe": 150.00,
    "fechaPago": "2026-01-05",
    "metodoPago": "transferencia",
    "categoria": "mensualidad"
  }')
echo "$RESPONSE" | jq .
INGRESO_ID=$(echo "$RESPONSE" | jq -r '.id')

echo -e "\n=== 2. Crear ingreso de fianza ==="
curl -s -X POST "$BASE_URL" \
  -H "Content-Type: application/json" \
  -d '{
    "contratoId": 1,
    "concepto": "Fianza inicial",
    "importe": 300.00,
    "fechaPago": "2026-01-01",
    "metodoPago": "efectivo",
    "categoria": "fianza"
  }' | jq .

echo -e "\n=== 3. Crear ingreso de penalización ==="
curl -s -X POST "$BASE_URL" \
  -H "Content-Type: application/json" \
  -d '{
    "contratoId": 1,
    "concepto": "Penalización por retraso",
    "importe": 25.00,
    "fechaPago": "2026-02-10",
    "metodoPago": "tarjeta",
    "categoria": "penalizacion"
  }' | jq .

echo -e "\n=== 4. Listar todos los ingresos ==="
curl -s "$BASE_URL" | jq .

echo -e "\n=== 5. Filtrar por categoría mensualidad ==="
curl -s "$BASE_URL?categoria=mensualidad" | jq .

echo -e "\n=== 6. Filtrar por contrato ==="
curl -s "$BASE_URL?contratoId=1" | jq .

echo -e "\n=== 7. Obtener ingreso $INGRESO_ID ==="
curl -s "$BASE_URL/$INGRESO_ID" | jq .

echo -e "\n=== 8. Actualizar ingreso $INGRESO_ID ==="
curl -s -X PUT "$BASE_URL/$INGRESO_ID" \
  -H "Content-Type: application/json" \
  -d '{
    "contratoId": 1,
    "concepto": "Mensualidad enero 2026 - Corregida",
    "importe": 155.00,
    "fechaPago": "2026-01-05",
    "metodoPago": "transferencia",
    "categoria": "mensualidad"
  }' | jq .

echo -e "\n=== 9. Eliminar ingreso $INGRESO_ID ==="
curl -s -X DELETE "$BASE_URL/$INGRESO_ID" -w "\nHTTP Status: %{http_code}\n"

echo -e "\n=== 10. Verificar eliminación ==="
curl -s "$BASE_URL/$INGRESO_ID" | jq .
```

Guardar como `test_ingreso_api.sh` y ejecutar:

```bash
chmod +x test_ingreso_api.sh
./test_ingreso_api.sh
```

---

## Notas

1. Debe existir un contrato válido antes de crear ingresos
2. El campo `metodoPago` es opcional
3. Las categorías y métodos de pago deben usar los valores exactos del enum
4. El importe debe ser positivo y tener máximo 2 decimales
5. El soft delete marca `deletedAt` pero no elimina físicamente el registro
6. Los ingresos eliminados no aparecen en listados por defecto
7. La fecha de pago debe estar en formato ISO 8601 (Y-m-d)
