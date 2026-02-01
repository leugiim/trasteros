# Módulo Direccion

Gestión de direcciones postales con validación de códigos postales y coordenadas geográficas, siguiendo arquitectura hexagonal con CQRS.

## Estructura

```
Direccion/
├── Application/          # Capa de Aplicación (Casos de Uso)
│   ├── Command/         # Comandos (Escritura)
│   │   ├── CreateDireccion/
│   │   ├── UpdateDireccion/
│   │   └── DeleteDireccion/
│   ├── Query/           # Consultas (Lectura)
│   │   ├── FindDireccion/
│   │   └── ListDirecciones/
│   └── DTO/             # Data Transfer Objects
│       ├── DireccionRequest.php
│       └── DireccionResponse.php
├── Domain/              # Capa de Dominio (Lógica de Negocio)
│   ├── Model/           # Entidades y Value Objects
│   │   ├── Direccion.php
│   │   ├── DireccionId.php
│   │   ├── CodigoPostal.php    # Value Object con validación
│   │   └── Coordenadas.php     # Value Object para lat/long
│   ├── Repository/      # Interfaces de Repositorio
│   │   └── DireccionRepositoryInterface.php
│   ├── Event/           # Eventos de Dominio
│   │   ├── DireccionCreated.php
│   │   ├── DireccionUpdated.php
│   │   └── DireccionDeleted.php
│   └── Exception/       # Excepciones de Dominio
│       ├── DireccionNotFoundException.php
│       ├── InvalidCodigoPostalException.php
│       └── InvalidCoordenadasException.php
└── Infrastructure/      # Capa de Infraestructura
    ├── Controller/
    │   └── DireccionController.php
    └── Persistence/Doctrine/Repository/
        └── DoctrineDireccionRepository.php
```

## API REST Endpoints

### Listar Direcciones
```http
GET /api/direcciones
GET /api/direcciones?ciudad=Madrid
GET /api/direcciones?provincia=Barcelona
GET /api/direcciones?codigoPostal=28001
GET /api/direcciones?onlyActive=true
```

**Response 200:**
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

### Obtener Dirección
```http
GET /api/direcciones/{id}
```

**Response 200:**
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

**Response 404:**
```json
{
  "error": {
    "message": "Direccion con ID 999 no encontrada",
    "code": "DIRECCION_NOT_FOUND"
  }
}
```

### Crear Dirección
```http
POST /api/direcciones
Content-Type: application/json

{
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
}
```

**Response 201:** (mismo formato que GET)

**Response 400 (validación):**
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

### Actualizar Dirección
```http
PUT /api/direcciones/{id}
Content-Type: application/json

{
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
}
```

**Response 200:** (mismo formato que GET)

**Response 404:** (dirección no encontrada)

### Eliminar Dirección
```http
DELETE /api/direcciones/{id}
```

**Response 204:** (sin contenido)

**Response 404:** (dirección no encontrada)

## Value Objects

### CodigoPostal
Valida códigos postales españoles:
- Formato: 5 dígitos (ej: 28013, 08001)
- Rango válido: 01000 a 52999
- Primer par de dígitos corresponde a la provincia

### Coordenadas
Valida coordenadas geográficas (latitud/longitud):
- Latitud: -90.0 a 90.0 grados
- Longitud: -180.0 a 180.0 grados
- Ambas opcionales (pueden ser null)
- Validación de rango automática

## Métodos del Repositorio

- `save(Direccion $direccion)`: Guardar/actualizar
- `remove(Direccion $direccion)`: Eliminar
- `findById(DireccionId $id)`: Buscar por ID
- `findAll()`: Listar todas (no eliminadas)
- `findByCiudad(string $ciudad)`: Filtrar por ciudad
- `findByProvincia(string $provincia)`: Filtrar por provincia
- `findByCodigoPostal(string $codigoPostal)`: Filtrar por código postal

## Método Útil

### direccionCompleta()
La entidad Direccion incluye el método `direccionCompleta()` que devuelve la dirección formateada:

```php
$direccion->direccionCompleta();
// "Calle Gran Vía 28, 3 A, 28013 Madrid, Madrid"
```

Este método construye la dirección completa incluyendo:
- Tipo de vía + nombre de vía
- Número (si existe)
- Piso y puerta (si existen)
- Código postal + ciudad + provincia

## Migración

**Archivo:** `migrations/Version20260201000003.php`

Crea la tabla `direccion` con:
- Campos: id, tipo_via, nombre_via, numero, piso, puerta, codigo_postal, ciudad, provincia, pais, latitud, longitud
- Timestamps: created_at, updated_at, deleted_at
- Auditoría: created_by, updated_by, deleted_by (FK a usuario)
- Índices en: codigo_postal, ciudad, provincia, deleted_at

**Ejecutar migración:**
```bash
php bin/console doctrine:migrations:migrate
```

## Características

1. **Validación de Código Postal español**: 5 dígitos, rango válido
2. **Validación de Coordenadas**: Latitud y longitud en rangos correctos
3. **Campos opcionales**: tipo_via, numero, piso, puerta, coordenadas
4. **Dirección completa formateada**: Método helper para obtener dirección en texto
5. **Soft Delete**: Las direcciones no se eliminan físicamente
6. **Auditoría**: Registra quién crea, modifica y elimina
7. **Filtros múltiples**: Por ciudad, provincia, código postal
8. **Geolocalización**: Soporte para latitud/longitud (opcional)
9. **CQRS**: Separación clara entre comandos y consultas
10. **Eventos de dominio**: DireccionCreated, DireccionUpdated, DireccionDeleted
