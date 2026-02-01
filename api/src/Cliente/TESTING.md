# Testing - Módulo Cliente

Ejemplos de pruebas de la API del módulo Cliente.

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

## Pruebas con cURL

### 1. Crear Cliente

#### Crear cliente completo
```bash
curl -X POST http://localhost:8000/api/clientes \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Juan",
    "apellidos": "García López",
    "dniNie": "12345678Z",
    "email": "juan.garcia@example.com",
    "telefono": "666123456",
    "activo": true
  }'
```

**Respuesta esperada (201 Created)**:
```json
{
  "id": 1,
  "nombre": "Juan",
  "apellidos": "García López",
  "nombreCompleto": "Juan García López",
  "dniNie": "12345678Z",
  "email": "juan.garcia@example.com",
  "telefono": "666123456",
  "activo": true,
  "createdAt": "2026-02-01T12:00:00+00:00",
  "updatedAt": "2026-02-01T12:00:00+00:00",
  "deletedAt": null
}
```

#### Crear cliente mínimo (solo nombre y apellidos)
```bash
curl -X POST http://localhost:8000/api/clientes \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "María",
    "apellidos": "Rodríguez Sánchez"
  }'
```

#### Crear cliente con NIE
```bash
curl -X POST http://localhost:8000/api/clientes \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Pedro",
    "apellidos": "Martínez López",
    "dniNie": "X1234567L",
    "email": "pedro@example.com",
    "telefono": "+34 612 34 56 78"
  }'
```

---

### 2. Listar Clientes

#### Listar todos los clientes
```bash
curl http://localhost:8000/api/clientes
```

**Respuesta esperada (200 OK)**:
```json
{
  "data": [
    {
      "id": 1,
      "nombre": "Juan",
      "apellidos": "García López",
      "nombreCompleto": "Juan García López",
      "dniNie": "12345678Z",
      "email": "juan.garcia@example.com",
      "telefono": "666123456",
      "activo": true,
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

#### Filtrar solo clientes activos
```bash
curl "http://localhost:8000/api/clientes?onlyActivos=true"
```

#### Buscar clientes por nombre
```bash
curl "http://localhost:8000/api/clientes?search=Juan"
```

#### Buscar por apellidos
```bash
curl "http://localhost:8000/api/clientes?search=García"
```

---

### 3. Obtener Cliente por ID

```bash
curl http://localhost:8000/api/clientes/1
```

**Respuesta esperada (200 OK)**:
```json
{
  "id": 1,
  "nombre": "Juan",
  "apellidos": "García López",
  "nombreCompleto": "Juan García López",
  ...
}
```

**Cliente no encontrado (404 Not Found)**:
```bash
curl http://localhost:8000/api/clientes/999
```

```json
{
  "error": {
    "message": "Cliente con ID 999 no encontrado",
    "code": "CLIENTE_NOT_FOUND"
  }
}
```

---

### 4. Actualizar Cliente

```bash
curl -X PUT http://localhost:8000/api/clientes/1 \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Juan Carlos",
    "apellidos": "García López",
    "dniNie": "12345678Z",
    "email": "juancarlos.garcia@example.com",
    "telefono": "666123456",
    "activo": true
  }'
```

**Respuesta esperada (200 OK)**: Cliente actualizado

#### Desactivar cliente
```bash
curl -X PUT http://localhost:8000/api/clientes/1 \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Juan",
    "apellidos": "García López",
    "dniNie": "12345678Z",
    "email": "juan.garcia@example.com",
    "telefono": "666123456",
    "activo": false
  }'
```

---

### 5. Eliminar Cliente

```bash
curl -X DELETE http://localhost:8000/api/clientes/1
```

**Respuesta esperada (204 No Content)**: Sin cuerpo de respuesta

---

## Casos de Error y Validación

### 1. DNI/NIE Duplicado

```bash
# Crear primer cliente
curl -X POST http://localhost:8000/api/clientes \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Cliente 1",
    "apellidos": "Apellido 1",
    "dniNie": "12345678Z"
  }'

# Intentar crear otro con el mismo DNI
curl -X POST http://localhost:8000/api/clientes \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Cliente 2",
    "apellidos": "Apellido 2",
    "dniNie": "12345678Z"
  }'
```

**Respuesta esperada (409 Conflict)**:
```json
{
  "error": {
    "message": "Ya existe un cliente con el DNI/NIE 12345678Z",
    "code": "CONFLICT"
  }
}
```

---

### 2. Email Duplicado

```bash
curl -X POST http://localhost:8000/api/clientes \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Cliente 1",
    "apellidos": "Apellido 1",
    "email": "mismo@example.com"
  }'

curl -X POST http://localhost:8000/api/clientes \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Cliente 2",
    "apellidos": "Apellido 2",
    "email": "mismo@example.com"
  }'
```

**Respuesta esperada (409 Conflict)**:
```json
{
  "error": {
    "message": "Ya existe un cliente con el email mismo@example.com",
    "code": "CONFLICT"
  }
}
```

---

### 3. DNI/NIE Inválido

#### Letra de control incorrecta
```bash
curl -X POST http://localhost:8000/api/clientes \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Test",
    "apellidos": "Test",
    "dniNie": "12345678A"
  }'
```

**Respuesta esperada (400 Bad Request)**:
```json
{
  "error": {
    "message": "Validation failed",
    "code": "VALIDATION_ERROR",
    "details": {
      "dniNie": ["El DNI/NIE \"12345678A\" tiene una letra de control inválida"]
    }
  }
}
```

#### Formato incorrecto
```bash
curl -X POST http://localhost:8000/api/clientes \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Test",
    "apellidos": "Test",
    "dniNie": "123ABC"
  }'
```

**Respuesta esperada (400 Bad Request)**:
```json
{
  "error": {
    "message": "Validation failed",
    "code": "VALIDATION_ERROR",
    "details": {
      "dniNie": ["El DNI/NIE \"123ABC\" no tiene un formato válido"]
    }
  }
}
```

---

### 4. Email Inválido

```bash
curl -X POST http://localhost:8000/api/clientes \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Test",
    "apellidos": "Test",
    "email": "email-invalido"
  }'
```

**Respuesta esperada (400 Bad Request)**:
```json
{
  "error": {
    "message": "Validation failed",
    "code": "VALIDATION_ERROR",
    "details": {
      "email": ["El email \"email-invalido\" no tiene un formato válido"]
    }
  }
}
```

---

### 5. Teléfono Inválido

```bash
curl -X POST http://localhost:8000/api/clientes \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Test",
    "apellidos": "Test",
    "telefono": "123"
  }'
```

**Respuesta esperada (400 Bad Request)**:
```json
{
  "error": {
    "message": "Validation failed",
    "code": "VALIDATION_ERROR",
    "details": {
      "telefono": ["El teléfono debe tener al menos 9 caracteres"]
    }
  }
}
```

---

### 6. Campos Obligatorios Faltantes

```bash
curl -X POST http://localhost:8000/api/clientes \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Test"
  }'
```

**Respuesta esperada (400 Bad Request)**:
```json
{
  "error": {
    "message": "Validation failed",
    "code": "VALIDATION_ERROR",
    "details": {
      "apellidos": ["Los apellidos son obligatorios"]
    }
  }
}
```

---

## Casos de Prueba Válidos

### DNIs Válidos
```bash
# DNI con letra correcta
12345678Z
87654321X
00000001R

# NIE con letra correcta
X1234567L
Y7654321Z
Z0000001T
```

### Emails Válidos
```bash
juan@example.com
maria.rodriguez@empresa.es
pedro+test@subdomain.example.com
contact_us@company-name.com
```

### Teléfonos Válidos
```bash
666123456           # Móvil español
+34 666 12 34 56    # Con prefijo internacional
912345678           # Fijo español
+1 (555) 123-4567   # Formato internacional
```

---

## Script de Prueba Completo

```bash
#!/bin/bash

BASE_URL="http://localhost:8000/api/clientes"

echo "=== 1. Crear cliente ==="
RESPONSE=$(curl -s -X POST "$BASE_URL" \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Juan",
    "apellidos": "García López",
    "dniNie": "12345678Z",
    "email": "juan@example.com",
    "telefono": "666123456",
    "activo": true
  }')
echo "$RESPONSE" | jq .
CLIENTE_ID=$(echo "$RESPONSE" | jq -r '.id')

echo -e "\n=== 2. Listar clientes ==="
curl -s "$BASE_URL" | jq .

echo -e "\n=== 3. Obtener cliente $CLIENTE_ID ==="
curl -s "$BASE_URL/$CLIENTE_ID" | jq .

echo -e "\n=== 4. Actualizar cliente $CLIENTE_ID ==="
curl -s -X PUT "$BASE_URL/$CLIENTE_ID" \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Juan Carlos",
    "apellidos": "García López",
    "dniNie": "12345678Z",
    "email": "juancarlos@example.com",
    "telefono": "666123456",
    "activo": true
  }' | jq .

echo -e "\n=== 5. Buscar por nombre ==="
curl -s "$BASE_URL?search=Juan" | jq .

echo -e "\n=== 6. Eliminar cliente $CLIENTE_ID ==="
curl -s -X DELETE "$BASE_URL/$CLIENTE_ID" -w "\nHTTP Status: %{http_code}\n"

echo -e "\n=== 7. Verificar eliminación ==="
curl -s "$BASE_URL/$CLIENTE_ID" | jq .
```

Guardar como `test_cliente_api.sh` y ejecutar:

```bash
chmod +x test_cliente_api.sh
./test_cliente_api.sh
```

---

## Notas

1. Los DNI/NIE incluyen validación de letra de control según el algoritmo oficial español
2. Los campos `dniNie`, `email` y `telefono` son opcionales pero deben ser válidos si se proporcionan
3. El soft delete marca `deletedAt` pero no elimina físicamente el registro
4. Los clientes eliminados no aparecen en listados por defecto
5. Las búsquedas son case-insensitive y buscan en nombre y apellidos
