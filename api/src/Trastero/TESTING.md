# Testing - Módulo Trastero

Ejemplos de pruebas de la API del módulo Trastero.

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

3. Tener al menos un local creado (necesario para crear trasteros)

## Pruebas con cURL

### 1. Crear Trastero

#### Crear trastero pequeño
```bash
curl -X POST http://localhost:8000/api/trasteros \
  -H "Content-Type: application/json" \
  -d '{
    "localId": 1,
    "numero": "A-101",
    "nombre": "Trastero pequeño zona A",
    "superficie": 3.50,
    "precioMensual": 50.00,
    "estado": "disponible"
  }'
```

**Respuesta esperada (201 Created)**:
```json
{
  "id": 1,
  "localId": 1,
  "numero": "A-101",
  "nombre": "Trastero pequeño zona A",
  "superficie": 3.50,
  "precioMensual": 50.00,
  "estado": "disponible",
  "createdAt": "2026-02-01T12:00:00+00:00",
  "updatedAt": "2026-02-01T12:00:00+00:00",
  "deletedAt": null
}
```

#### Crear trastero mediano
```bash
curl -X POST http://localhost:8000/api/trasteros \
  -H "Content-Type: application/json" \
  -d '{
    "localId": 1,
    "numero": "B-201",
    "nombre": "Trastero mediano zona B",
    "superficie": 8.00,
    "precioMensual": 100.00,
    "estado": "disponible"
  }'
```

#### Crear trastero grande
```bash
curl -X POST http://localhost:8000/api/trasteros \
  -H "Content-Type: application/json" \
  -d '{
    "localId": 1,
    "numero": "C-301",
    "nombre": "Trastero grande zona C",
    "superficie": 15.00,
    "precioMensual": 180.00,
    "estado": "disponible"
  }'
```

#### Crear trastero sin nombre (opcional)
```bash
curl -X POST http://localhost:8000/api/trasteros \
  -H "Content-Type: application/json" \
  -d '{
    "localId": 1,
    "numero": "A-102",
    "superficie": 5.50,
    "precioMensual": 75.00,
    "estado": "disponible"
  }'
```

#### Crear trastero sin especificar estado (por defecto: disponible)
```bash
curl -X POST http://localhost:8000/api/trasteros \
  -H "Content-Type: application/json" \
  -d '{
    "localId": 1,
    "numero": "A-103",
    "nombre": "Trastero pequeño",
    "superficie": 4.00,
    "precioMensual": 60.00
  }'
```

#### Crear trastero en mantenimiento
```bash
curl -X POST http://localhost:8000/api/trasteros \
  -H "Content-Type: application/json" \
  -d '{
    "localId": 1,
    "numero": "A-104",
    "nombre": "Trastero en reparación",
    "superficie": 5.00,
    "precioMensual": 70.00,
    "estado": "mantenimiento"
  }'
```

---

### 2. Listar Trasteros

#### Listar todos los trasteros
```bash
curl http://localhost:8000/api/trasteros
```

**Respuesta esperada (200 OK)**:
```json
{
  "data": [
    {
      "id": 1,
      "localId": 1,
      "numero": "A-101",
      "nombre": "Trastero pequeño zona A",
      "superficie": 3.50,
      "precioMensual": 50.00,
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

#### Filtrar por local
```bash
curl "http://localhost:8000/api/trasteros?localId=1"
```

#### Filtrar por estado disponible
```bash
curl "http://localhost:8000/api/trasteros?estado=disponible"
```

#### Filtrar por estado ocupado
```bash
curl "http://localhost:8000/api/trasteros?estado=ocupado"
```

#### Filtrar por estado en mantenimiento
```bash
curl "http://localhost:8000/api/trasteros?estado=mantenimiento"
```

#### Filtrar solo activos (no eliminados)
```bash
curl "http://localhost:8000/api/trasteros?onlyActive=true"
```

#### Combinar filtros
```bash
curl "http://localhost:8000/api/trasteros?localId=1&estado=disponible"
```

---

### 3. Obtener Trastero por ID

```bash
curl http://localhost:8000/api/trasteros/1
```

**Respuesta esperada (200 OK)**:
```json
{
  "id": 1,
  "localId": 1,
  "numero": "A-101",
  "nombre": "Trastero pequeño zona A",
  "superficie": 3.50,
  "precioMensual": 50.00,
  "estado": "disponible",
  ...
}
```

**Trastero no encontrado (404 Not Found)**:
```bash
curl http://localhost:8000/api/trasteros/999
```

```json
{
  "error": {
    "message": "Trastero con ID 999 no encontrado",
    "code": "TRASTERO_NOT_FOUND"
  }
}
```

---

### 4. Actualizar Trastero

#### Actualizar precio
```bash
curl -X PUT http://localhost:8000/api/trasteros/1 \
  -H "Content-Type: application/json" \
  -d '{
    "localId": 1,
    "numero": "A-101",
    "nombre": "Trastero pequeño zona A",
    "superficie": 3.50,
    "precioMensual": 55.00,
    "estado": "disponible"
  }'
```

**Respuesta esperada (200 OK)**: Trastero actualizado

#### Cambiar estado a ocupado
```bash
curl -X PUT http://localhost:8000/api/trasteros/1 \
  -H "Content-Type: application/json" \
  -d '{
    "localId": 1,
    "numero": "A-101",
    "nombre": "Trastero pequeño zona A",
    "superficie": 3.50,
    "precioMensual": 50.00,
    "estado": "ocupado"
  }'
```

#### Cambiar estado a mantenimiento
```bash
curl -X PUT http://localhost:8000/api/trasteros/1 \
  -H "Content-Type: application/json" \
  -d '{
    "localId": 1,
    "numero": "A-101",
    "nombre": "Trastero pequeño zona A",
    "superficie": 3.50,
    "precioMensual": 50.00,
    "estado": "mantenimiento"
  }'
```

#### Cambiar estado a reservado
```bash
curl -X PUT http://localhost:8000/api/trasteros/1 \
  -H "Content-Type: application/json" \
  -d '{
    "localId": 1,
    "numero": "A-101",
    "nombre": "Trastero pequeño zona A",
    "superficie": 3.50,
    "precioMensual": 50.00,
    "estado": "reservado"
  }'
```

---

### 5. Eliminar Trastero

```bash
curl -X DELETE http://localhost:8000/api/trasteros/1
```

**Respuesta esperada (204 No Content)**: Sin cuerpo de respuesta

---

## Casos de Error y Validación

### 1. Local No Existe

```bash
curl -X POST http://localhost:8000/api/trasteros \
  -H "Content-Type: application/json" \
  -d '{
    "localId": 999,
    "numero": "A-101",
    "superficie": 5.00,
    "precioMensual": 70.00,
    "estado": "disponible"
  }'
```

**Respuesta esperada (400 Bad Request)**:
```json
{
  "error": {
    "message": "Validation failed",
    "code": "VALIDATION_ERROR",
    "details": {
      "localId": ["Local con ID 999 no encontrado"]
    }
  }
}
```

---

### 2. Trastero Duplicado (mismo número en el mismo local)

```bash
# Crear primer trastero
curl -X POST http://localhost:8000/api/trasteros \
  -H "Content-Type: application/json" \
  -d '{
    "localId": 1,
    "numero": "A-101",
    "superficie": 5.00,
    "precioMensual": 70.00,
    "estado": "disponible"
  }'

# Intentar crear otro con el mismo número
curl -X POST http://localhost:8000/api/trasteros \
  -H "Content-Type: application/json" \
  -d '{
    "localId": 1,
    "numero": "A-101",
    "superficie": 6.00,
    "precioMensual": 80.00,
    "estado": "disponible"
  }'
```

**Respuesta esperada (409 Conflict)**:
```json
{
  "error": {
    "message": "Ya existe un trastero con número 'A-101' en el local 1",
    "code": "DUPLICATE_TRASTERO"
  }
}
```

---

### 3. Superficie Inválida

#### Superficie negativa
```bash
curl -X POST http://localhost:8000/api/trasteros \
  -H "Content-Type: application/json" \
  -d '{
    "localId": 1,
    "numero": "A-101",
    "superficie": -5.00,
    "precioMensual": 70.00,
    "estado": "disponible"
  }'
```

**Respuesta esperada (400 Bad Request)**:
```json
{
  "error": {
    "message": "Validation failed",
    "code": "VALIDATION_ERROR",
    "details": {
      "superficie": ["La superficie debe ser mayor que 0"]
    }
  }
}
```

#### Superficie cero
```bash
curl -X POST http://localhost:8000/api/trasteros \
  -H "Content-Type: application/json" \
  -d '{
    "localId": 1,
    "numero": "A-101",
    "superficie": 0.00,
    "precioMensual": 70.00,
    "estado": "disponible"
  }'
```

**Respuesta esperada (400 Bad Request)**:
```json
{
  "error": {
    "message": "Validation failed",
    "code": "VALIDATION_ERROR",
    "details": {
      "superficie": ["La superficie debe ser mayor que 0"]
    }
  }
}
```

---

### 4. Precio Mensual Inválido

#### Precio negativo
```bash
curl -X POST http://localhost:8000/api/trasteros \
  -H "Content-Type: application/json" \
  -d '{
    "localId": 1,
    "numero": "A-101",
    "superficie": 5.00,
    "precioMensual": -50.00,
    "estado": "disponible"
  }'
```

**Respuesta esperada (400 Bad Request)**:
```json
{
  "error": {
    "message": "Validation failed",
    "code": "VALIDATION_ERROR",
    "details": {
      "precioMensual": ["El precio mensual debe ser mayor que 0"]
    }
  }
}
```

#### Precio cero
```bash
curl -X POST http://localhost:8000/api/trasteros \
  -H "Content-Type: application/json" \
  -d '{
    "localId": 1,
    "numero": "A-101",
    "superficie": 5.00,
    "precioMensual": 0.00,
    "estado": "disponible"
  }'
```

**Respuesta esperada (400 Bad Request)**:
```json
{
  "error": {
    "message": "Validation failed",
    "code": "VALIDATION_ERROR",
    "details": {
      "precioMensual": ["El precio mensual debe ser mayor que 0"]
    }
  }
}
```

---

### 5. Estado Inválido

```bash
curl -X POST http://localhost:8000/api/trasteros \
  -H "Content-Type: application/json" \
  -d '{
    "localId": 1,
    "numero": "A-101",
    "superficie": 5.00,
    "precioMensual": 70.00,
    "estado": "estado_invalido"
  }'
```

**Respuesta esperada (400 Bad Request)**:
```json
{
  "error": {
    "message": "Validation failed",
    "code": "VALIDATION_ERROR",
    "details": {
      "estado": ["El estado 'estado_invalido' no es válido. Valores permitidos: disponible, ocupado, mantenimiento, reservado"]
    }
  }
}
```

---

### 6. Campos Obligatorios Faltantes

```bash
curl -X POST http://localhost:8000/api/trasteros \
  -H "Content-Type: application/json" \
  -d '{
    "localId": 1
  }'
```

**Respuesta esperada (400 Bad Request)**:
```json
{
  "error": {
    "message": "Validation failed",
    "code": "VALIDATION_ERROR",
    "details": {
      "numero": ["El número es obligatorio"],
      "superficie": ["La superficie es obligatoria"],
      "precioMensual": ["El precio mensual es obligatorio"]
    }
  }
}
```

---

## Casos de Prueba Válidos

### Estados Válidos
```bash
disponible
ocupado
mantenimiento
reservado
```

### Superficies Válidas (m²)
```bash
1.00      # Trastero muy pequeño
3.50      # Trastero pequeño
5.50      # Trastero pequeño-mediano
8.00      # Trastero mediano
12.00     # Trastero grande
15.00     # Trastero muy grande
20.00     # Trastero extra grande
```

### Precios Mensuales Válidos
```bash
30.00     # Precio bajo
50.00     # Precio pequeño
75.00     # Precio mediano-bajo
100.00    # Precio mediano
150.00    # Precio alto
200.00    # Precio muy alto
```

### Números de Trastero Válidos
```bash
# Por zona
A-101, A-102, A-103
B-201, B-202, B-203
C-301, C-302, C-303

# Por planta
P1-001, P1-002, P1-003
P2-001, P2-002, P2-003

# Por sótano
S1-001, S1-002, S1-003
S2-001, S2-002, S2-003

# Numérico simple
001, 002, 003
101, 102, 103
```

---

## Script de Prueba Completo

```bash
#!/bin/bash

BASE_URL="http://localhost:8000/api/trasteros"

echo "=== 1. Crear trastero pequeño ==="
RESPONSE=$(curl -s -X POST "$BASE_URL" \
  -H "Content-Type: application/json" \
  -d '{
    "localId": 1,
    "numero": "A-101",
    "nombre": "Trastero pequeño zona A",
    "superficie": 3.50,
    "precioMensual": 50.00,
    "estado": "disponible"
  }')
echo "$RESPONSE" | jq .
TRASTERO_ID=$(echo "$RESPONSE" | jq -r '.id')

echo -e "\n=== 2. Crear trastero mediano ==="
curl -s -X POST "$BASE_URL" \
  -H "Content-Type: application/json" \
  -d '{
    "localId": 1,
    "numero": "B-201",
    "nombre": "Trastero mediano zona B",
    "superficie": 8.00,
    "precioMensual": 100.00,
    "estado": "disponible"
  }' | jq .

echo -e "\n=== 3. Crear trastero grande ==="
curl -s -X POST "$BASE_URL" \
  -H "Content-Type: application/json" \
  -d '{
    "localId": 1,
    "numero": "C-301",
    "nombre": "Trastero grande zona C",
    "superficie": 15.00,
    "precioMensual": 180.00,
    "estado": "disponible"
  }' | jq .

echo -e "\n=== 4. Listar todos los trasteros ==="
curl -s "$BASE_URL" | jq .

echo -e "\n=== 5. Filtrar por estado disponible ==="
curl -s "$BASE_URL?estado=disponible" | jq .

echo -e "\n=== 6. Filtrar por local ==="
curl -s "$BASE_URL?localId=1" | jq .

echo -e "\n=== 7. Obtener trastero $TRASTERO_ID ==="
curl -s "$BASE_URL/$TRASTERO_ID" | jq .

echo -e "\n=== 8. Cambiar estado a ocupado ==="
curl -s -X PUT "$BASE_URL/$TRASTERO_ID" \
  -H "Content-Type: application/json" \
  -d '{
    "localId": 1,
    "numero": "A-101",
    "nombre": "Trastero pequeño zona A",
    "superficie": 3.50,
    "precioMensual": 50.00,
    "estado": "ocupado"
  }' | jq .

echo -e "\n=== 9. Actualizar precio ==="
curl -s -X PUT "$BASE_URL/$TRASTERO_ID" \
  -H "Content-Type: application/json" \
  -d '{
    "localId": 1,
    "numero": "A-101",
    "nombre": "Trastero pequeño zona A",
    "superficie": 3.50,
    "precioMensual": 55.00,
    "estado": "ocupado"
  }' | jq .

echo -e "\n=== 10. Eliminar trastero $TRASTERO_ID ==="
curl -s -X DELETE "$BASE_URL/$TRASTERO_ID" -w "\nHTTP Status: %{http_code}\n"

echo -e "\n=== 11. Verificar eliminación ==="
curl -s "$BASE_URL/$TRASTERO_ID" | jq .
```

Guardar como `test_trastero_api.sh` y ejecutar:

```bash
chmod +x test_trastero_api.sh
./test_trastero_api.sh
```

---

## Notas

1. Debe existir un local válido antes de crear trasteros
2. La combinación de `localId` + `numero` debe ser única
3. El campo `nombre` es opcional
4. El campo `estado` es opcional (por defecto: disponible)
5. La superficie y el precio mensual deben ser positivos con máximo 2 decimales
6. El soft delete marca `deletedAt` pero no elimina físicamente el registro
7. Los trasteros eliminados no aparecen en listados por defecto
8. Se recomienda usar convenciones claras para la numeración (por zona, planta, etc.)
