# Testing - Módulo Préstamo

Ejemplos de pruebas de la API del módulo Préstamo.

## Prerrequisitos

1. Ejecutar las migraciones:
```bash
cd api
php bin/console doctrine:migrations:migrate
```

2. Iniciar el servidor de desarrollo:
```bash
cd api
symfony server:start
# o
php -S localhost:8000 -t public/
```

3. Asegurarse de tener al menos un Local creado (para las relaciones).

## Pruebas con cURL

### 1. Crear un Préstamo

#### Préstamo completo
```bash
curl -X POST http://localhost:8000/api/prestamos \
  -H "Content-Type: application/json" \
  -d '{
    "localId": 1,
    "entidadBancaria": "Banco Santander",
    "numeroPrestamo": "PRE-2024-001",
    "capitalSolicitado": 200000.00,
    "totalADevolver": 240000.00,
    "tipoInteres": 4.5000,
    "fechaConcesion": "2024-01-20",
    "estado": "activo"
  }'
```

**Respuesta esperada (201 Created)**:
```json
{
  "id": 1,
  "localId": 1,
  "localNombre": "Nombre del Local",
  "entidadBancaria": "Banco Santander",
  "numeroPrestamo": "PRE-2024-001",
  "capitalSolicitado": 200000,
  "totalADevolver": 240000,
  "tipoInteres": 4.5,
  "fechaConcesion": "2024-01-20",
  "estado": "activo",
  "createdAt": "2024-01-20T10:30:00+00:00",
  "updatedAt": "2024-01-20T10:30:00+00:00",
  "deletedAt": null
}
```

#### Préstamo con campos opcionales nulos
```bash
curl -X POST http://localhost:8000/api/prestamos \
  -H "Content-Type: application/json" \
  -d '{
    "localId": 1,
    "capitalSolicitado": 150000.00,
    "totalADevolver": 165000.00,
    "fechaConcesion": "2024-02-01",
    "estado": "activo"
  }'
```

---

### 2. Listar Todos los Préstamos

```bash
curl http://localhost:8000/api/prestamos
```

**Respuesta esperada (200 OK)**:
```json
{
  "data": [
    {
      "id": 1,
      "localId": 1,
      "localNombre": "Local Centro",
      "entidadBancaria": "Banco Santander",
      "numeroPrestamo": "PRE-2024-001",
      "capitalSolicitado": 200000,
      "totalADevolver": 240000,
      "tipoInteres": 4.5,
      "fechaConcesion": "2024-01-20",
      "estado": "activo",
      "createdAt": "2024-01-20T10:30:00+00:00",
      "updatedAt": "2024-01-20T10:30:00+00:00",
      "deletedAt": null
    }
  ],
  "meta": {
    "total": 1
  }
}
```

---

### 3. Obtener un Préstamo por ID

```bash
curl http://localhost:8000/api/prestamos/1
```

**Respuesta esperada (200 OK)**: Igual que un elemento del listado

**Préstamo no encontrado (404)**:
```bash
curl http://localhost:8000/api/prestamos/999
```

```json
{
  "error": {
    "message": "Prestamo with id 999 not found",
    "code": "PRESTAMO_NOT_FOUND"
  }
}
```

---

### 4. Filtros de Búsqueda

#### Listar préstamos de un local específico
```bash
curl "http://localhost:8000/api/prestamos?localId=1"
```

#### Listar préstamos por estado
```bash
curl "http://localhost:8000/api/prestamos?estado=activo"
```

#### Listar préstamos por entidad bancaria
```bash
curl "http://localhost:8000/api/prestamos?entidadBancaria=Santander"
```

#### Listar solo préstamos activos (no eliminados)
```bash
curl "http://localhost:8000/api/prestamos?onlyActive=true"
```

---

### 5. Actualizar un Préstamo

```bash
curl -X PUT http://localhost:8000/api/prestamos/1 \
  -H "Content-Type: application/json" \
  -d '{
    "localId": 1,
    "entidadBancaria": "BBVA",
    "numeroPrestamo": "PRE-2024-001-MOD",
    "capitalSolicitado": 200000.00,
    "totalADevolver": 235000.00,
    "tipoInteres": 4.2000,
    "fechaConcesion": "2024-01-20",
    "estado": "activo"
  }'
```

**Respuesta esperada (200 OK)**: Préstamo actualizado

---

### 6. Cambiar Estado de un Préstamo

#### Finalizar préstamo
```bash
curl -X PUT http://localhost:8000/api/prestamos/1 \
  -H "Content-Type: application/json" \
  -d '{
    "localId": 1,
    "entidadBancaria": "BBVA",
    "numeroPrestamo": "PRE-2024-001",
    "capitalSolicitado": 200000.00,
    "totalADevolver": 235000.00,
    "tipoInteres": 4.2000,
    "fechaConcesion": "2024-01-20",
    "estado": "finalizado"
  }'
```

#### Cancelar préstamo
```bash
curl -X PUT http://localhost:8000/api/prestamos/1 \
  -H "Content-Type: application/json" \
  -d '{
    "localId": 1,
    "entidadBancaria": "BBVA",
    "numeroPrestamo": "PRE-2024-001",
    "capitalSolicitado": 200000.00,
    "totalADevolver": 235000.00,
    "tipoInteres": 4.2000,
    "fechaConcesion": "2024-01-20",
    "estado": "cancelado"
  }'
```

---

### 7. Eliminar un Préstamo

```bash
curl -X DELETE http://localhost:8000/api/prestamos/1
```

**Respuesta esperada (204 No Content)**: Sin cuerpo de respuesta

---

## Casos de Error y Validación

### 1. Crear Préstamo sin Capital Solicitado

```bash
curl -X POST http://localhost:8000/api/prestamos \
  -H "Content-Type: application/json" \
  -d '{
    "localId": 1,
    "totalADevolver": 240000.00,
    "fechaConcesion": "2024-01-20",
    "estado": "activo"
  }'
```

**Respuesta esperada (400 Bad Request)**:
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

### 2. Crear Préstamo con Capital Negativo

```bash
curl -X POST http://localhost:8000/api/prestamos \
  -H "Content-Type: application/json" \
  -d '{
    "localId": 1,
    "capitalSolicitado": -5000.00,
    "totalADevolver": 240000.00,
    "fechaConcesion": "2024-01-20",
    "estado": "activo"
  }'
```

**Respuesta esperada (400 Bad Request)**:
```json
{
  "error": {
    "message": "Validation failed",
    "code": "VALIDATION_ERROR",
    "details": {
      "capitalSolicitado": ["El capital solicitado no puede ser negativo o cero: -5000"]
    }
  }
}
```

---

### 3. Crear Préstamo con Estado Inválido

```bash
curl -X POST http://localhost:8000/api/prestamos \
  -H "Content-Type: application/json" \
  -d '{
    "localId": 1,
    "capitalSolicitado": 200000.00,
    "totalADevolver": 240000.00,
    "fechaConcesion": "2024-01-20",
    "estado": "invalido"
  }'
```

**Respuesta esperada (400 Bad Request)**:
```json
{
  "error": {
    "message": "Validation failed",
    "code": "VALIDATION_ERROR",
    "details": {
      "estado": ["Estado de préstamo inválido: invalido. Valores permitidos: activo, cancelado, finalizado"]
    }
  }
}
```

---

### 4. Crear Préstamo con Local Inexistente

```bash
curl -X POST http://localhost:8000/api/prestamos \
  -H "Content-Type: application/json" \
  -d '{
    "localId": 99999,
    "capitalSolicitado": 200000.00,
    "totalADevolver": 240000.00,
    "fechaConcesion": "2024-01-20",
    "estado": "activo"
  }'
```

**Respuesta esperada (400 Bad Request)**:
```json
{
  "error": {
    "message": "Validation failed",
    "code": "VALIDATION_ERROR",
    "details": {
      "localId": ["Local with id 99999 not found"]
    }
  }
}
```

---

### 5. Crear Préstamo con Fecha Inválida

```bash
curl -X POST http://localhost:8000/api/prestamos \
  -H "Content-Type: application/json" \
  -d '{
    "localId": 1,
    "capitalSolicitado": 200000.00,
    "totalADevolver": 240000.00,
    "fechaConcesion": "2024-13-45",
    "estado": "activo"
  }'
```

**Respuesta esperada (400 Bad Request)**:
```json
{
  "error": {
    "message": "Validation failed",
    "code": "VALIDATION_ERROR",
    "details": {
      "fechaConcesion": ["Invalid date format. Expected Y-m-d"]
    }
  }
}
```

---

### 6. Tipo de Interés Negativo

```bash
curl -X POST http://localhost:8000/api/prestamos \
  -H "Content-Type: application/json" \
  -d '{
    "localId": 1,
    "capitalSolicitado": 200000.00,
    "totalADevolver": 240000.00,
    "tipoInteres": -2.5,
    "fechaConcesion": "2024-01-20",
    "estado": "activo"
  }'
```

**Respuesta esperada (400 Bad Request)**:
```json
{
  "error": {
    "message": "Validation failed",
    "code": "VALIDATION_ERROR",
    "details": {
      "tipoInteres": ["El tipo de interés no puede ser negativo: -2.5"]
    }
  }
}
```

---

### 7. Capital Solicitado Excede el Máximo

```bash
curl -X POST http://localhost:8000/api/prestamos \
  -H "Content-Type: application/json" \
  -d '{
    "localId": 1,
    "capitalSolicitado": 1000000000.00,
    "totalADevolver": 1100000000.00,
    "fechaConcesion": "2024-01-20",
    "estado": "activo"
  }'
```

**Respuesta esperada (400 Bad Request)**:
```json
{
  "error": {
    "message": "Validation failed",
    "code": "VALIDATION_ERROR",
    "details": {
      "capitalSolicitado": ["El capital solicitado no puede exceder 999999999.99"]
    }
  }
}
```

---

## Casos de Prueba Válidos

### Valores de Capital y Total

```bash
# Valores mínimos
capitalSolicitado: 0.01
totalADevolver: 0.01

# Valores normales
capitalSolicitado: 150000.00
totalADevolver: 180000.00

# Valores máximos
capitalSolicitado: 999999999.99
totalADevolver: 999999999.99
```

### Tipos de Interés Válidos

```bash
0.0000      # Sin interés
3.5000      # 3.5%
4.2500      # 4.25%
12.9999     # 12.9999%
99.9999     # Máximo permitido
```

### Estados Válidos

```bash
activo      # Préstamo activo
cancelado   # Préstamo cancelado
finalizado  # Préstamo finalizado
```

---

## Script de Prueba Completo

```bash
#!/bin/bash

BASE_URL="http://localhost:8000/api/prestamos"

echo "=== 1. Creando préstamo ==="
RESPONSE=$(curl -s -X POST "$BASE_URL" \
  -H "Content-Type: application/json" \
  -d '{
    "localId": 1,
    "entidadBancaria": "Banco Santander",
    "numeroPrestamo": "PRE-2024-001",
    "capitalSolicitado": 200000.00,
    "totalADevolver": 240000.00,
    "tipoInteres": 4.5000,
    "fechaConcesion": "2024-01-20",
    "estado": "activo"
  }')
echo "$RESPONSE" | jq .

PRESTAMO_ID=$(echo "$RESPONSE" | jq -r '.id')
echo "Préstamo creado con ID: $PRESTAMO_ID"

echo -e "\n=== 2. Listando préstamos ==="
curl -s "$BASE_URL" | jq .

echo -e "\n=== 3. Obteniendo préstamo $PRESTAMO_ID ==="
curl -s "$BASE_URL/$PRESTAMO_ID" | jq .

echo -e "\n=== 4. Actualizando préstamo $PRESTAMO_ID ==="
curl -s -X PUT "$BASE_URL/$PRESTAMO_ID" \
  -H "Content-Type: application/json" \
  -d '{
    "localId": 1,
    "entidadBancaria": "BBVA",
    "numeroPrestamo": "PRE-2024-001",
    "capitalSolicitado": 200000.00,
    "totalADevolver": 235000.00,
    "tipoInteres": 4.2000,
    "fechaConcesion": "2024-01-20",
    "estado": "activo"
  }' | jq .

echo -e "\n=== 5. Eliminando préstamo $PRESTAMO_ID ==="
curl -s -X DELETE "$BASE_URL/$PRESTAMO_ID" -w "\nHTTP Status: %{http_code}\n"

echo -e "\n=== 6. Verificando eliminación ==="
curl -s "$BASE_URL/$PRESTAMO_ID" | jq .
```

Guardar como `test_prestamo_api.sh` y ejecutar:

```bash
chmod +x test_prestamo_api.sh
./test_prestamo_api.sh
```

---

## Verificación de la Base de Datos

Después de crear algunos préstamos, puedes verificar los datos directamente:

```bash
cd api
php bin/console doctrine:query:sql "SELECT * FROM prestamo"
```

O si usas SQLite:

```bash
sqlite3 api/var/data.db "SELECT * FROM prestamo"
```

---

## Notas Importantes

1. Asegúrate de que el servidor esté corriendo antes de ejecutar las pruebas
2. Los IDs son autoincrementales, ajusta según tu base de datos
3. Debes tener al menos un Local creado antes de crear préstamos
4. Los campos `entidadBancaria`, `numeroPrestamo` y `tipoInteres` son opcionales
5. El soft delete marca `deletedAt` pero no elimina físicamente el registro
6. Los préstamos eliminados no aparecen en listados por defecto (a menos que uses `onlyActive=false`)
