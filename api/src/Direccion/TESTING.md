# Testing - Módulo Direccion

Ejemplos de pruebas de la API del módulo Direccion.

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

### 1. Crear Dirección

#### Crear dirección completa con coordenadas
```bash
curl -X POST http://localhost:8000/api/direcciones \
  -H "Content-Type: application/json" \
  -d '{
    "tipoVia": "Calle",
    "nombreVia": "Gran Vía",
    "numero": "28",
    "piso": "3",
    "puerta": "A",
    "codigoPostal": "28013",
    "ciudad": "Madrid",
    "provincia": "Madrid",
    "pais": "España",
    "latitud": 40.4200,
    "longitud": -3.7050
  }'
```

**Respuesta esperada (201 Created)**:
```json
{
  "id": 1,
  "tipoVia": "Calle",
  "nombreVia": "Gran Vía",
  "numero": "28",
  "piso": "3",
  "puerta": "A",
  "codigoPostal": "28013",
  "ciudad": "Madrid",
  "provincia": "Madrid",
  "pais": "España",
  "latitud": 40.4200,
  "longitud": -3.7050,
  "direccionCompleta": "Calle Gran Vía 28, 3 A, 28013 Madrid, Madrid",
  "createdAt": "2026-02-01T12:00:00+00:00",
  "updatedAt": "2026-02-01T12:00:00+00:00",
  "deletedAt": null
}
```

#### Crear dirección mínima (solo campos obligatorios)
```bash
curl -X POST http://localhost:8000/api/direcciones \
  -H "Content-Type: application/json" \
  -d '{
    "nombreVia": "Paseo de la Castellana",
    "codigoPostal": "28046",
    "ciudad": "Madrid",
    "provincia": "Madrid",
    "pais": "España"
  }'
```

#### Crear dirección en Barcelona
```bash
curl -X POST http://localhost:8000/api/direcciones \
  -H "Content-Type: application/json" \
  -d '{
    "tipoVia": "Avenida",
    "nombreVia": "Diagonal",
    "numero": "450",
    "codigoPostal": "08006",
    "ciudad": "Barcelona",
    "provincia": "Barcelona",
    "pais": "España",
    "latitud": 41.3954,
    "longitud": 2.1619
  }'
```

#### Crear dirección sin coordenadas
```bash
curl -X POST http://localhost:8000/api/direcciones \
  -H "Content-Type: application/json" \
  -d '{
    "tipoVia": "Plaza",
    "nombreVia": "Mayor",
    "numero": "1",
    "codigoPostal": "28012",
    "ciudad": "Madrid",
    "provincia": "Madrid",
    "pais": "España"
  }'
```

---

### 2. Listar Direcciones

#### Listar todas las direcciones
```bash
curl http://localhost:8000/api/direcciones
```

**Respuesta esperada (200 OK)**:
```json
{
  "data": [
    {
      "id": 1,
      "tipoVia": "Calle",
      "nombreVia": "Gran Vía",
      "numero": "28",
      "piso": "3",
      "puerta": "A",
      "codigoPostal": "28013",
      "ciudad": "Madrid",
      "provincia": "Madrid",
      "pais": "España",
      "latitud": 40.4200,
      "longitud": -3.7050,
      "direccionCompleta": "Calle Gran Vía 28, 3 A, 28013 Madrid, Madrid",
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

#### Filtrar por ciudad
```bash
curl "http://localhost:8000/api/direcciones?ciudad=Madrid"
```

#### Filtrar por provincia
```bash
curl "http://localhost:8000/api/direcciones?provincia=Barcelona"
```

#### Filtrar por código postal
```bash
curl "http://localhost:8000/api/direcciones?codigoPostal=28013"
```

#### Filtrar solo activas (no eliminadas)
```bash
curl "http://localhost:8000/api/direcciones?onlyActive=true"
```

#### Combinar filtros
```bash
curl "http://localhost:8000/api/direcciones?ciudad=Madrid&codigoPostal=28013"
```

---

### 3. Obtener Dirección por ID

```bash
curl http://localhost:8000/api/direcciones/1
```

**Respuesta esperada (200 OK)**:
```json
{
  "id": 1,
  "tipoVia": "Calle",
  "nombreVia": "Gran Vía",
  "numero": "28",
  ...
}
```

**Dirección no encontrada (404 Not Found)**:
```bash
curl http://localhost:8000/api/direcciones/999
```

```json
{
  "error": {
    "message": "Direccion con ID 999 no encontrada",
    "code": "DIRECCION_NOT_FOUND"
  }
}
```

---

### 4. Actualizar Dirección

```bash
curl -X PUT http://localhost:8000/api/direcciones/1 \
  -H "Content-Type: application/json" \
  -d '{
    "tipoVia": "Calle",
    "nombreVia": "Gran Vía",
    "numero": "30",
    "piso": "4",
    "puerta": "B",
    "codigoPostal": "28013",
    "ciudad": "Madrid",
    "provincia": "Madrid",
    "pais": "España",
    "latitud": 40.4200,
    "longitud": -3.7050
  }'
```

**Respuesta esperada (200 OK)**: Dirección actualizada

#### Actualizar solo coordenadas
```bash
curl -X PUT http://localhost:8000/api/direcciones/1 \
  -H "Content-Type: application/json" \
  -d '{
    "tipoVia": "Calle",
    "nombreVia": "Gran Vía",
    "numero": "28",
    "piso": "3",
    "puerta": "A",
    "codigoPostal": "28013",
    "ciudad": "Madrid",
    "provincia": "Madrid",
    "pais": "España",
    "latitud": 40.4210,
    "longitud": -3.7060
  }'
```

---

### 5. Eliminar Dirección

```bash
curl -X DELETE http://localhost:8000/api/direcciones/1
```

**Respuesta esperada (204 No Content)**: Sin cuerpo de respuesta

---

## Casos de Error y Validación

### 1. Código Postal Inválido

#### Formato incorrecto (no 5 dígitos)
```bash
curl -X POST http://localhost:8000/api/direcciones \
  -H "Content-Type: application/json" \
  -d '{
    "nombreVia": "Test",
    "codigoPostal": "280",
    "ciudad": "Madrid",
    "provincia": "Madrid",
    "pais": "España"
  }'
```

**Respuesta esperada (400 Bad Request)**:
```json
{
  "error": {
    "message": "Validation failed",
    "code": "VALIDATION_ERROR",
    "details": {
      "codigoPostal": ["El código postal debe tener 5 dígitos"]
    }
  }
}
```

#### Código postal fuera de rango
```bash
curl -X POST http://localhost:8000/api/direcciones \
  -H "Content-Type: application/json" \
  -d '{
    "nombreVia": "Test",
    "codigoPostal": "99999",
    "ciudad": "Test",
    "provincia": "Test",
    "pais": "España"
  }'
```

**Respuesta esperada (400 Bad Request)**:
```json
{
  "error": {
    "message": "Validation failed",
    "code": "VALIDATION_ERROR",
    "details": {
      "codigoPostal": ["El código postal debe estar entre 01000 y 52999"]
    }
  }
}
```

---

### 2. Coordenadas Inválidas

#### Latitud fuera de rango
```bash
curl -X POST http://localhost:8000/api/direcciones \
  -H "Content-Type: application/json" \
  -d '{
    "nombreVia": "Test",
    "codigoPostal": "28013",
    "ciudad": "Madrid",
    "provincia": "Madrid",
    "pais": "España",
    "latitud": 95.0,
    "longitud": -3.7050
  }'
```

**Respuesta esperada (400 Bad Request)**:
```json
{
  "error": {
    "message": "Validation failed",
    "code": "VALIDATION_ERROR",
    "details": {
      "coordenadas": ["La latitud debe estar entre -90 y 90 grados"]
    }
  }
}
```

#### Longitud fuera de rango
```bash
curl -X POST http://localhost:8000/api/direcciones \
  -H "Content-Type: application/json" \
  -d '{
    "nombreVia": "Test",
    "codigoPostal": "28013",
    "ciudad": "Madrid",
    "provincia": "Madrid",
    "pais": "España",
    "latitud": 40.4200,
    "longitud": 200.0
  }'
```

**Respuesta esperada (400 Bad Request)**:
```json
{
  "error": {
    "message": "Validation failed",
    "code": "VALIDATION_ERROR",
    "details": {
      "coordenadas": ["La longitud debe estar entre -180 y 180 grados"]
    }
  }
}
```

#### Solo una coordenada (inválido - deben ser ambas o ninguna)
```bash
curl -X POST http://localhost:8000/api/direcciones \
  -H "Content-Type: application/json" \
  -d '{
    "nombreVia": "Test",
    "codigoPostal": "28013",
    "ciudad": "Madrid",
    "provincia": "Madrid",
    "pais": "España",
    "latitud": 40.4200
  }'
```

**Respuesta esperada (400 Bad Request)**:
```json
{
  "error": {
    "message": "Validation failed",
    "code": "VALIDATION_ERROR",
    "details": {
      "coordenadas": ["Si se proporciona latitud, también debe proporcionarse longitud y viceversa"]
    }
  }
}
```

---

### 3. Campos Obligatorios Faltantes

```bash
curl -X POST http://localhost:8000/api/direcciones \
  -H "Content-Type: application/json" \
  -d '{
    "nombreVia": "Test"
  }'
```

**Respuesta esperada (400 Bad Request)**:
```json
{
  "error": {
    "message": "Validation failed",
    "code": "VALIDATION_ERROR",
    "details": {
      "codigoPostal": ["El código postal es obligatorio"],
      "ciudad": ["La ciudad es obligatoria"],
      "provincia": ["La provincia es obligatoria"]
    }
  }
}
```

---

## Casos de Prueba Válidos

### Códigos Postales Válidos (España)
```bash
01000  # Vitoria-Gasteiz (Álava)
08001  # Barcelona
28001  # Madrid
41001  # Sevilla
46001  # Valencia
50001  # Zaragoza
```

### Coordenadas Válidas (España)
```bash
# Madrid
Latitud: 40.4168, Longitud: -3.7038

# Barcelona
Latitud: 41.3851, Longitud: 2.1734

# Valencia
Latitud: 39.4699, Longitud: -0.3763

# Sevilla
Latitud: 37.3891, Longitud: -5.9845
```

### Tipos de Vía Comunes
```bash
Calle
Avenida
Plaza
Paseo
Ronda
Travesía
Camino
Carretera
```

---

## Script de Prueba Completo

```bash
#!/bin/bash

BASE_URL="http://localhost:8000/api/direcciones"

echo "=== 1. Crear dirección en Madrid ==="
RESPONSE=$(curl -s -X POST "$BASE_URL" \
  -H "Content-Type: application/json" \
  -d '{
    "tipoVia": "Calle",
    "nombreVia": "Gran Vía",
    "numero": "28",
    "piso": "3",
    "puerta": "A",
    "codigoPostal": "28013",
    "ciudad": "Madrid",
    "provincia": "Madrid",
    "pais": "España",
    "latitud": 40.4200,
    "longitud": -3.7050
  }')
echo "$RESPONSE" | jq .
DIRECCION_ID=$(echo "$RESPONSE" | jq -r '.id')

echo -e "\n=== 2. Crear dirección en Barcelona ==="
curl -s -X POST "$BASE_URL" \
  -H "Content-Type: application/json" \
  -d '{
    "tipoVia": "Avenida",
    "nombreVia": "Diagonal",
    "numero": "450",
    "codigoPostal": "08006",
    "ciudad": "Barcelona",
    "provincia": "Barcelona",
    "pais": "España",
    "latitud": 41.3954,
    "longitud": 2.1619
  }' | jq .

echo -e "\n=== 3. Listar todas las direcciones ==="
curl -s "$BASE_URL" | jq .

echo -e "\n=== 4. Filtrar por ciudad ==="
curl -s "$BASE_URL?ciudad=Madrid" | jq .

echo -e "\n=== 5. Obtener dirección $DIRECCION_ID ==="
curl -s "$BASE_URL/$DIRECCION_ID" | jq .

echo -e "\n=== 6. Actualizar dirección $DIRECCION_ID ==="
curl -s -X PUT "$BASE_URL/$DIRECCION_ID" \
  -H "Content-Type: application/json" \
  -d '{
    "tipoVia": "Calle",
    "nombreVia": "Gran Vía",
    "numero": "30",
    "piso": "4",
    "puerta": "B",
    "codigoPostal": "28013",
    "ciudad": "Madrid",
    "provincia": "Madrid",
    "pais": "España",
    "latitud": 40.4200,
    "longitud": -3.7050
  }' | jq .

echo -e "\n=== 7. Eliminar dirección $DIRECCION_ID ==="
curl -s -X DELETE "$BASE_URL/$DIRECCION_ID" -w "\nHTTP Status: %{http_code}\n"

echo -e "\n=== 8. Verificar eliminación ==="
curl -s "$BASE_URL/$DIRECCION_ID" | jq .
```

Guardar como `test_direccion_api.sh` y ejecutar:

```bash
chmod +x test_direccion_api.sh
./test_direccion_api.sh
```

---

## Notas

1. Los campos `tipoVia`, `numero`, `piso`, `puerta` y coordenadas son opcionales
2. Las coordenadas deben proporcionarse ambas o ninguna
3. El código postal debe ser válido para España (5 dígitos, rango 01000-52999)
4. El soft delete marca `deletedAt` pero no elimina físicamente el registro
5. Las direcciones eliminadas no aparecen en listados por defecto
6. El método `direccionCompleta` formatea automáticamente la dirección completa
