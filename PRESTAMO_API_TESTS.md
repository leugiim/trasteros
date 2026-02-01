# Módulo Prestamo - Pruebas de API

Esta guía contiene ejemplos de pruebas para todos los endpoints del módulo Prestamo.

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

**Respuesta esperada (201 Created):**
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
  "createdAt": "2024-01-20 10:30:00",
  "updatedAt": "2024-01-20 10:30:00",
  "deletedAt": null
}
```

### 2. Crear un Préstamo con Campos Opcionales Nulos

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

### 3. Listar Todos los Préstamos

```bash
curl http://localhost:8000/api/prestamos
```

**Respuesta esperada (200 OK):**
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
      "createdAt": "2024-01-20 10:30:00",
      "updatedAt": "2024-01-20 10:30:00",
      "deletedAt": null
    }
  ],
  "meta": {
    "total": 1
  }
}
```

### 4. Obtener un Préstamo por ID

```bash
curl http://localhost:8000/api/prestamos/1
```

### 5. Listar Préstamos Filtrados por Local

```bash
curl http://localhost:8000/api/prestamos?localId=1
```

### 6. Listar Préstamos Filtrados por Estado

```bash
curl http://localhost:8000/api/prestamos?estado=activo
```

### 7. Listar Préstamos Filtrados por Entidad Bancaria

```bash
curl "http://localhost:8000/api/prestamos?entidadBancaria=Santander"
```

### 8. Listar Solo Préstamos Activos (No Eliminados)

```bash
curl http://localhost:8000/api/prestamos?onlyActive=true
```

### 9. Actualizar un Préstamo

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

### 10. Cambiar Estado de un Préstamo a "finalizado"

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

### 11. Eliminar un Préstamo

```bash
curl -X DELETE http://localhost:8000/api/prestamos/1
```

**Respuesta esperada (204 No Content):** Sin cuerpo de respuesta

## Pruebas de Validación (Casos de Error)

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

**Respuesta esperada (400 Bad Request):**
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

**Respuesta esperada (400 Bad Request):**
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

**Respuesta esperada (400 Bad Request):**
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

**Respuesta esperada (400 Bad Request):**
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

**Respuesta esperada (400 Bad Request):**
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

### 6. Obtener Préstamo Inexistente

```bash
curl http://localhost:8000/api/prestamos/99999
```

**Respuesta esperada (404 Not Found):**
```json
{
  "error": {
    "message": "Prestamo with id 99999 not found",
    "code": "PRESTAMO_NOT_FOUND"
  }
}
```

### 7. Tipo de Interés Negativo

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

**Respuesta esperada (400 Bad Request):**
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

## Pruebas con Postman/Insomnia

### Collection de Postman

Puedes importar esta colección en Postman:

```json
{
  "info": {
    "name": "Prestamo API",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "item": [
    {
      "name": "Listar Préstamos",
      "request": {
        "method": "GET",
        "url": "http://localhost:8000/api/prestamos"
      }
    },
    {
      "name": "Obtener Préstamo",
      "request": {
        "method": "GET",
        "url": "http://localhost:8000/api/prestamos/1"
      }
    },
    {
      "name": "Crear Préstamo",
      "request": {
        "method": "POST",
        "url": "http://localhost:8000/api/prestamos",
        "header": [
          {
            "key": "Content-Type",
            "value": "application/json"
          }
        ],
        "body": {
          "mode": "raw",
          "raw": "{\n  \"localId\": 1,\n  \"entidadBancaria\": \"Banco Santander\",\n  \"numeroPrestamo\": \"PRE-2024-001\",\n  \"capitalSolicitado\": 200000.00,\n  \"totalADevolver\": 240000.00,\n  \"tipoInteres\": 4.5000,\n  \"fechaConcesion\": \"2024-01-20\",\n  \"estado\": \"activo\"\n}"
        }
      }
    },
    {
      "name": "Actualizar Préstamo",
      "request": {
        "method": "PUT",
        "url": "http://localhost:8000/api/prestamos/1",
        "header": [
          {
            "key": "Content-Type",
            "value": "application/json"
          }
        ],
        "body": {
          "mode": "raw",
          "raw": "{\n  \"localId\": 1,\n  \"entidadBancaria\": \"BBVA\",\n  \"numeroPrestamo\": \"PRE-2024-001\",\n  \"capitalSolicitado\": 200000.00,\n  \"totalADevolver\": 235000.00,\n  \"tipoInteres\": 4.2000,\n  \"fechaConcesion\": \"2024-01-20\",\n  \"estado\": \"activo\"\n}"
        }
      }
    },
    {
      "name": "Eliminar Préstamo",
      "request": {
        "method": "DELETE",
        "url": "http://localhost:8000/api/prestamos/1"
      }
    }
  ]
}
```

## Verificación de la Base de Datos

Después de crear algunos préstamos, puedes verificar los datos directamente en la base de datos:

```bash
cd api
php bin/console doctrine:query:sql "SELECT * FROM prestamo"
```

O si estás usando SQLite:

```bash
sqlite3 api/var/data.db "SELECT * FROM prestamo"
```

## Scripts de Prueba Automatizados

Puedes crear un script bash para probar todos los endpoints:

```bash
#!/bin/bash

BASE_URL="http://localhost:8000/api/prestamos"

echo "1. Creando préstamo..."
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

echo -e "\n2. Listando préstamos..."
curl -s "$BASE_URL" | jq .

echo -e "\n3. Obteniendo préstamo $PRESTAMO_ID..."
curl -s "$BASE_URL/$PRESTAMO_ID" | jq .

echo -e "\n4. Actualizando préstamo $PRESTAMO_ID..."
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

echo -e "\n5. Eliminando préstamo $PRESTAMO_ID..."
curl -s -X DELETE "$BASE_URL/$PRESTAMO_ID" -w "\nHTTP Status: %{http_code}\n"

echo -e "\n6. Verificando eliminación..."
curl -s "$BASE_URL/$PRESTAMO_ID" | jq .
```

Guarda este script como `test_prestamo_api.sh` y ejecútalo:

```bash
chmod +x test_prestamo_api.sh
./test_prestamo_api.sh
```

## Notas Importantes

1. Asegúrate de que el servidor esté corriendo antes de ejecutar las pruebas.
2. Los IDs de los préstamos son autoincrementales, ajusta los IDs en los ejemplos según tu base de datos.
3. Para las pruebas de creación, asegúrate de que exista el Local con el ID especificado.
4. El módulo incluye validaciones robustas, por lo que los datos incorrectos serán rechazados con mensajes de error descriptivos.
5. Los campos opcionales (`entidadBancaria`, `numeroPrestamo`, `tipoInteres`) pueden omitirse en las peticiones POST/PUT.
