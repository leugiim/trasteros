# Ejemplos de Uso - API Local

Colección de ejemplos usando cURL para probar el módulo Local.

## Variables de Entorno
```bash
API_URL="http://localhost:8000"
```

## 1. Crear un Local

```bash
curl -X POST "$API_URL/api/locales" \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Local Comercial Centro",
    "direccionId": 1,
    "superficieTotal": 150.75,
    "numeroTrasteros": 20,
    "fechaCompra": "2024-01-15",
    "precioCompra": 350000.00,
    "referenciaCatastral": "1234567VK1234A0001AB",
    "valorCatastral": 400000.00
  }'
```

## 2. Crear un Local Mínimo (solo campos obligatorios)

```bash
curl -X POST "$API_URL/api/locales" \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Local Simple",
    "direccionId": 1
  }'
```

## 3. Listar Todos los Locales

```bash
curl -X GET "$API_URL/api/locales"
```

## 4. Listar Solo Locales Activos

```bash
curl -X GET "$API_URL/api/locales?onlyActive=true"
```

## 5. Buscar Locales por Nombre

```bash
curl -X GET "$API_URL/api/locales?nombre=Centro"
```

## 6. Buscar Locales por Dirección

```bash
curl -X GET "$API_URL/api/locales?direccionId=1"
```

## 7. Obtener un Local Específico

```bash
curl -X GET "$API_URL/api/locales/1"
```

## 8. Actualizar un Local Completo

```bash
curl -X PUT "$API_URL/api/locales/1" \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Local Comercial Centro Actualizado",
    "direccionId": 1,
    "superficieTotal": 160.00,
    "numeroTrasteros": 25,
    "fechaCompra": "2024-01-15",
    "precioCompra": 375000.00,
    "referenciaCatastral": "1234567VK1234A0001AB",
    "valorCatastral": 425000.00
  }'
```

## 9. Eliminar un Local

```bash
curl -X DELETE "$API_URL/api/locales/1"
```

## Casos de Error

### Error: Local no encontrado (404)
```bash
curl -X GET "$API_URL/api/locales/999"
```

**Respuesta:**
```json
{
  "error": {
    "message": "Local with id \"999\" not found",
    "code": "LOCAL_NOT_FOUND"
  }
}
```

### Error: Dirección no encontrada (400)
```bash
curl -X POST "$API_URL/api/locales" \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Local Test",
    "direccionId": 999
  }'
```

**Respuesta:**
```json
{
  "error": {
    "message": "Validation failed",
    "code": "VALIDATION_ERROR",
    "details": {
      "direccionId": ["Direccion with id \"999\" not found"]
    }
  }
}
```

### Error: Referencia catastral inválida (400)
```bash
curl -X POST "$API_URL/api/locales" \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Local Test",
    "direccionId": 1,
    "referenciaCatastral": "12345678901234567890123456789012345678901234567890ABC"
  }'
```

**Respuesta:**
```json
{
  "error": {
    "message": "Validation failed",
    "code": "VALIDATION_ERROR",
    "details": {
      "referenciaCatastral": ["La referencia catastral \"...\" no puede superar los 50 caracteres"]
    }
  }
}
```

### Error: Fecha inválida (400)
```bash
curl -X POST "$API_URL/api/locales" \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Local Test",
    "direccionId": 1,
    "fechaCompra": "fecha-invalida"
  }'
```

**Respuesta:**
```json
{
  "error": {
    "message": "Validation failed",
    "code": "VALIDATION_ERROR",
    "details": {
      "fechaCompra": ["Failed to parse time string..."]
    }
  }
}
```

### Error: Validación de campos obligatorios (400)
```bash
curl -X POST "$API_URL/api/locales" \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "",
    "direccionId": 0
  }'
```

## Script de Prueba Completo

```bash
#!/bin/bash

API_URL="http://localhost:8000"

echo "=== Creando primer local ==="
LOCAL1=$(curl -s -X POST "$API_URL/api/locales" \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Local Plaza Mayor",
    "direccionId": 1,
    "superficieTotal": 120.50,
    "numeroTrasteros": 15,
    "fechaCompra": "2024-01-15",
    "precioCompra": 250000.00,
    "referenciaCatastral": "1234567VK1234A0001AB",
    "valorCatastral": 300000.00
  }')
echo $LOCAL1 | jq '.'

LOCAL1_ID=$(echo $LOCAL1 | jq -r '.id')
echo "ID del local creado: $LOCAL1_ID"

echo ""
echo "=== Creando segundo local ==="
LOCAL2=$(curl -s -X POST "$API_URL/api/locales" \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Local Centro Comercial",
    "direccionId": 1,
    "superficieTotal": 200.00,
    "numeroTrasteros": 30
  }')
echo $LOCAL2 | jq '.'

echo ""
echo "=== Listando todos los locales ==="
curl -s -X GET "$API_URL/api/locales" | jq '.'

echo ""
echo "=== Obteniendo local específico ==="
curl -s -X GET "$API_URL/api/locales/$LOCAL1_ID" | jq '.'

echo ""
echo "=== Actualizando local ==="
curl -s -X PUT "$API_URL/api/locales/$LOCAL1_ID" \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Local Plaza Mayor Renovado",
    "direccionId": 1,
    "superficieTotal": 125.00,
    "numeroTrasteros": 20,
    "fechaCompra": "2024-01-15",
    "precioCompra": 260000.00,
    "referenciaCatastral": "1234567VK1234A0001AB",
    "valorCatastral": 310000.00
  }' | jq '.'

echo ""
echo "=== Buscando por nombre ==="
curl -s -X GET "$API_URL/api/locales?nombre=Plaza" | jq '.'

echo ""
echo "=== Eliminando local ==="
curl -s -X DELETE "$API_URL/api/locales/$LOCAL1_ID" -w "\nHTTP Status: %{http_code}\n"

echo ""
echo "=== Verificando eliminación ==="
curl -s -X GET "$API_URL/api/locales/$LOCAL1_ID" | jq '.'
```

Guarda este script como `test_local_api.sh` y ejecútalo con:
```bash
chmod +x test_local_api.sh
./test_local_api.sh
```

## Integración con Postman

Importa esta colección en Postman:

```json
{
  "info": {
    "name": "Trasteros - Local API",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "variable": [
    {
      "key": "baseUrl",
      "value": "http://localhost:8000",
      "type": "string"
    }
  ],
  "item": [
    {
      "name": "List Locales",
      "request": {
        "method": "GET",
        "url": "{{baseUrl}}/api/locales"
      }
    },
    {
      "name": "Get Local",
      "request": {
        "method": "GET",
        "url": "{{baseUrl}}/api/locales/1"
      }
    },
    {
      "name": "Create Local",
      "request": {
        "method": "POST",
        "url": "{{baseUrl}}/api/locales",
        "header": [
          {
            "key": "Content-Type",
            "value": "application/json"
          }
        ],
        "body": {
          "mode": "raw",
          "raw": "{\n  \"nombre\": \"Local Test\",\n  \"direccionId\": 1,\n  \"superficieTotal\": 100.00,\n  \"numeroTrasteros\": 10,\n  \"fechaCompra\": \"2024-01-15\",\n  \"precioCompra\": 200000.00,\n  \"referenciaCatastral\": \"ABC123\",\n  \"valorCatastral\": 250000.00\n}"
        }
      }
    },
    {
      "name": "Update Local",
      "request": {
        "method": "PUT",
        "url": "{{baseUrl}}/api/locales/1",
        "header": [
          {
            "key": "Content-Type",
            "value": "application/json"
          }
        ],
        "body": {
          "mode": "raw",
          "raw": "{\n  \"nombre\": \"Local Actualizado\",\n  \"direccionId\": 1,\n  \"superficieTotal\": 120.00,\n  \"numeroTrasteros\": 15,\n  \"fechaCompra\": \"2024-01-15\",\n  \"precioCompra\": 220000.00,\n  \"referenciaCatastral\": \"ABC123\",\n  \"valorCatastral\": 270000.00\n}"
        }
      }
    },
    {
      "name": "Delete Local",
      "request": {
        "method": "DELETE",
        "url": "{{baseUrl}}/api/locales/1"
      }
    }
  ]
}
```
