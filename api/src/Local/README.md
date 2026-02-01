# Módulo Local

Módulo completo para la gestión de locales siguiendo arquitectura hexagonal con CQRS.

## Estructura

```
Local/
├── Application/
│   ├── Command/
│   │   ├── CreateLocal/
│   │   │   ├── CreateLocalCommand.php
│   │   │   └── CreateLocalCommandHandler.php
│   │   ├── UpdateLocal/
│   │   │   ├── UpdateLocalCommand.php
│   │   │   └── UpdateLocalCommandHandler.php
│   │   └── DeleteLocal/
│   │       ├── DeleteLocalCommand.php
│   │       └── DeleteLocalCommandHandler.php
│   ├── Query/
│   │   ├── FindLocal/
│   │   │   ├── FindLocalQuery.php
│   │   │   └── FindLocalQueryHandler.php
│   │   └── ListLocales/
│   │       ├── ListLocalesQuery.php
│   │       └── ListLocalesQueryHandler.php
│   └── DTO/
│       ├── LocalRequest.php
│       └── LocalResponse.php
├── Domain/
│   ├── Model/
│   │   ├── Local.php
│   │   ├── LocalId.php
│   │   └── ReferenciaCatastral.php
│   ├── Repository/
│   │   └── LocalRepositoryInterface.php
│   ├── Event/
│   │   ├── LocalCreated.php
│   │   ├── LocalUpdated.php
│   │   └── LocalDeleted.php
│   └── Exception/
│       ├── LocalNotFoundException.php
│       └── InvalidReferenciaCatastralException.php
└── Infrastructure/
    ├── Persistence/Doctrine/Repository/
    │   └── DoctrineLocalRepository.php
    └── Controller/
        └── LocalController.php
```

## API Endpoints

### Listar Locales
```http
GET /api/locales
GET /api/locales?onlyActive=true
GET /api/locales?nombre=Plaza
GET /api/locales?direccionId=1
```

**Respuesta 200 OK:**
```json
{
  "data": [
    {
      "id": 1,
      "nombre": "Local Plaza Mayor",
      "direccion": {
        "id": 1,
        "tipoVia": "Plaza",
        "nombreVia": "Mayor",
        "numero": "1",
        "ciudad": "Madrid",
        "provincia": "Madrid",
        "codigoPostal": "28012",
        "pais": "España",
        "direccionCompleta": "Plaza Mayor 1, 28012 Madrid, Madrid"
      },
      "superficieTotal": 120.50,
      "numeroTrasteros": 15,
      "fechaCompra": "2024-01-15",
      "precioCompra": 250000.00,
      "referenciaCatastral": "1234567VK1234A0001AB",
      "valorCatastral": 300000.00,
      "createdAt": "2024-01-15T10:00:00+00:00",
      "updatedAt": "2024-01-15T10:00:00+00:00",
      "deletedAt": null
    }
  ],
  "meta": {
    "total": 1
  }
}
```

### Obtener Local por ID
```http
GET /api/locales/{id}
```

**Respuesta 200 OK:**
```json
{
  "id": 1,
  "nombre": "Local Plaza Mayor",
  "direccion": { ... },
  "superficieTotal": 120.50,
  "numeroTrasteros": 15,
  "fechaCompra": "2024-01-15",
  "precioCompra": 250000.00,
  "referenciaCatastral": "1234567VK1234A0001AB",
  "valorCatastral": 300000.00,
  "createdAt": "2024-01-15T10:00:00+00:00",
  "updatedAt": "2024-01-15T10:00:00+00:00",
  "deletedAt": null
}
```

**Respuesta 404 Not Found:**
```json
{
  "error": {
    "message": "Local with id \"999\" not found",
    "code": "LOCAL_NOT_FOUND"
  }
}
```

### Crear Local
```http
POST /api/locales
Content-Type: application/json

{
  "nombre": "Local Plaza Mayor",
  "direccionId": 1,
  "superficieTotal": 120.50,
  "numeroTrasteros": 15,
  "fechaCompra": "2024-01-15",
  "precioCompra": 250000.00,
  "referenciaCatastral": "1234567VK1234A0001AB",
  "valorCatastral": 300000.00
}
```

**Respuesta 201 Created:**
```json
{
  "id": 1,
  "nombre": "Local Plaza Mayor",
  "direccion": { ... },
  "superficieTotal": 120.50,
  "numeroTrasteros": 15,
  "fechaCompra": "2024-01-15",
  "precioCompra": 250000.00,
  "referenciaCatastral": "1234567VK1234A0001AB",
  "valorCatastral": 300000.00,
  "createdAt": "2024-01-15T10:00:00+00:00",
  "updatedAt": "2024-01-15T10:00:00+00:00",
  "deletedAt": null
}
```

**Respuesta 400 Bad Request (Validación):**
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

### Actualizar Local
```http
PUT /api/locales/{id}
Content-Type: application/json

{
  "nombre": "Local Plaza Mayor Actualizado",
  "direccionId": 1,
  "superficieTotal": 125.00,
  "numeroTrasteros": 20,
  "fechaCompra": "2024-01-15",
  "precioCompra": 260000.00,
  "referenciaCatastral": "1234567VK1234A0001AB",
  "valorCatastral": 310000.00
}
```

**Respuesta 200 OK:**
```json
{
  "id": 1,
  "nombre": "Local Plaza Mayor Actualizado",
  "direccion": { ... },
  "superficieTotal": 125.00,
  "numeroTrasteros": 20,
  "fechaCompra": "2024-01-15",
  "precioCompra": 260000.00,
  "referenciaCatastral": "1234567VK1234A0001AB",
  "valorCatastral": 310000.00,
  "createdAt": "2024-01-15T10:00:00+00:00",
  "updatedAt": "2024-01-15T11:00:00+00:00",
  "deletedAt": null
}
```

### Eliminar Local
```http
DELETE /api/locales/{id}
```

**Respuesta 204 No Content:**
```
(cuerpo vacío)
```

**Respuesta 404 Not Found:**
```json
{
  "error": {
    "message": "Local with id \"999\" not found",
    "code": "LOCAL_NOT_FOUND"
  }
}
```

## Modelo de Datos

### Campos Obligatorios
- `nombre`: Nombre del local (string, max 255 caracteres)
- `direccionId`: ID de la dirección (integer)

### Campos Opcionales
- `superficieTotal`: Superficie total en m² (decimal 10,2)
- `numeroTrasteros`: Cantidad de trasteros (integer)
- `fechaCompra`: Fecha de compra (date, formato: YYYY-MM-DD)
- `precioCompra`: Precio de compra (decimal 12,2)
- `referenciaCatastral`: Referencia catastral (string, max 50 caracteres)
- `valorCatastral`: Valor catastral (decimal 12,2)

### Campos Automáticos
- `id`: Generado automáticamente
- `createdAt`: Timestamp de creación
- `updatedAt`: Timestamp de última actualización
- `deletedAt`: Timestamp de eliminación (soft delete)
- `createdBy`, `updatedBy`, `deletedBy`: Referencias a usuarios

## Value Objects

### LocalId
Value object inmutable que representa el ID del local.

```php
$localId = LocalId::fromInt(1);
echo $localId->value; // 1
echo (string) $localId; // "1"
$localId->equals(LocalId::fromInt(1)); // true
```

### ReferenciaCatastral
Value object inmutable que representa una referencia catastral con validación.

```php
$referencia = ReferenciaCatastral::fromString('1234567VK1234A0001AB');
echo $referencia->value; // "1234567VK1234A0001AB"

// Validaciones:
// - No puede estar vacía
// - Máximo 50 caracteres
```

## Eventos de Dominio

### LocalCreated
Disparado cuando se crea un nuevo local.

### LocalUpdated
Disparado cuando se actualiza un local existente.

### LocalDeleted
Disparado cuando se elimina un local.

## Excepciones

### LocalNotFoundException
Lanzada cuando no se encuentra un local por su ID.

### InvalidReferenciaCatastralException
Lanzada cuando la referencia catastral es inválida (vacía o muy larga).

## Relaciones

### ManyToOne con Direccion
Cada local está asociado a una dirección. La relación es obligatoria.

```php
$local->direccion(); // Retorna un objeto Direccion
```

### ManyToOne con User
Campos de auditoría que registran qué usuario realizó cada acción.

- `createdBy`: Usuario que creó el local
- `updatedBy`: Usuario que actualizó el local por última vez
- `deletedBy`: Usuario que eliminó el local

## Filtros de Búsqueda

El endpoint de listado soporta los siguientes filtros:

- `onlyActive`: Retorna solo locales no eliminados (true/false)
- `nombre`: Busca locales por nombre (búsqueda parcial con LIKE)
- `direccionId`: Filtra locales por dirección específica
