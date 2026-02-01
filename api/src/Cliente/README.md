# Módulo Cliente

Gestión de clientes que alquilan trasteros siguiendo arquitectura hexagonal con CQRS.

## Estructura

```
Cliente/
├── Application/          # Capa de Aplicación (Casos de Uso)
│   ├── Command/         # Comandos (Escritura)
│   │   ├── CreateCliente/
│   │   ├── UpdateCliente/
│   │   └── DeleteCliente/
│   ├── Query/           # Consultas (Lectura)
│   │   ├── FindCliente/
│   │   └── ListClientes/
│   └── DTO/             # Data Transfer Objects
│       ├── ClienteRequest.php
│       └── ClienteResponse.php
├── Domain/              # Capa de Dominio (Lógica de Negocio)
│   ├── Model/           # Entidades y Value Objects
│   │   ├── Cliente.php
│   │   ├── ClienteId.php
│   │   ├── DniNie.php       # Value Object con validación española
│   │   ├── Email.php        # Value Object con validación
│   │   └── Telefono.php     # Value Object con validación
│   ├── Repository/      # Interfaces de Repositorio
│   │   └── ClienteRepositoryInterface.php
│   ├── Event/           # Eventos de Dominio
│   │   ├── ClienteCreated.php
│   │   ├── ClienteUpdated.php
│   │   └── ClienteDeleted.php
│   └── Exception/       # Excepciones de Dominio
│       ├── ClienteNotFoundException.php
│       ├── DuplicatedDniNieException.php
│       ├── DuplicatedEmailException.php
│       ├── InvalidDniNieException.php
│       ├── InvalidEmailException.php
│       └── InvalidTelefonoException.php
└── Infrastructure/      # Capa de Infraestructura
    ├── Controller/
    │   └── ClienteController.php
    └── Persistence/Doctrine/Repository/
        └── DoctrineClienteRepository.php
```

## API REST Endpoints

### Listar Clientes
```http
GET /api/clientes
GET /api/clientes?search=juan
GET /api/clientes?onlyActivos=true
```

**Response 200:**
```json
{
  "data": [
    {
      "id": 1,
      "nombre": "Juan",
      "apellidos": "García López",
      "nombreCompleto": "Juan García López",
      "dniNie": "12345678Z",
      "email": "juan@example.com",
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

### Obtener Cliente
```http
GET /api/clientes/{id}
```

**Response 200:**
```json
{
  "id": 1,
  "nombre": "Juan",
  "apellidos": "García López",
  "nombreCompleto": "Juan García López",
  "dniNie": "12345678Z",
  "email": "juan@example.com",
  "telefono": "666123456",
  "activo": true,
  "createdAt": "2026-02-01T12:00:00+00:00",
  "updatedAt": "2026-02-01T12:00:00+00:00",
  "deletedAt": null
}
```

**Response 404:**
```json
{
  "error": {
    "message": "Cliente con ID 999 no encontrado",
    "code": "CLIENTE_NOT_FOUND"
  }
}
```

### Crear Cliente
```http
POST /api/clientes
Content-Type: application/json

{
  "nombre": "Juan",
  "apellidos": "García López",
  "dniNie": "12345678Z",
  "email": "juan@example.com",
  "telefono": "666123456",
  "activo": true
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
      "dniNie": ["El DNI/NIE \"12345678A\" tiene una letra de control inválida"],
      "email": ["El email \"invalid\" no tiene un formato válido"]
    }
  }
}
```

**Response 409 (conflicto):**
```json
{
  "error": {
    "message": "Ya existe un cliente con el DNI/NIE 12345678Z",
    "code": "CONFLICT"
  }
}
```

### Actualizar Cliente
```http
PUT /api/clientes/{id}
Content-Type: application/json

{
  "nombre": "Juan",
  "apellidos": "García López",
  "dniNie": "12345678Z",
  "email": "juan.nuevo@example.com",
  "telefono": "666123456",
  "activo": true
}
```

**Response 200:** (mismo formato que GET)

**Response 404:** (cliente no encontrado)

### Eliminar Cliente
```http
DELETE /api/clientes/{id}
```

**Response 204:** (sin contenido)

**Response 404:** (cliente no encontrado)

## Value Objects

### DniNie
Valida DNI y NIE españoles con letra de control:
- Formato: 8 dígitos + 1 letra (ej: 12345678Z)
- NIE: Comienza con X, Y o Z (ej: X1234567L)
- Verifica letra de control según algoritmo oficial

### Email
Valida formato de email:
- Formato estándar RFC 5322
- Máximo 255 caracteres
- Se normaliza a minúsculas

### Telefono
Valida formato de teléfono:
- Acepta números, espacios, guiones, paréntesis
- Puede incluir prefijo internacional (+)
- Entre 9 y 20 caracteres

## Métodos del Repositorio

- `save(Cliente $cliente)`: Guardar/actualizar
- `remove(Cliente $cliente)`: Eliminar
- `findById(ClienteId $id)`: Buscar por ID
- `findByDniNie(string $dniNie)`: Buscar por DNI/NIE
- `findByEmail(string $email)`: Buscar por email
- `findAll()`: Listar todos (no eliminados)
- `findActivos()`: Listar solo activos
- `searchByNombreOrApellidos(string $term)`: Búsqueda por nombre/apellidos
- `existsByDniNie(string $dniNie, ?int $excludeId)`: Verificar duplicado
- `existsByEmail(string $email, ?int $excludeId)`: Verificar duplicado

## Método Útil

### nombreCompleto()
La entidad Cliente incluye el método `nombreCompleto()` que devuelve la concatenación de nombre y apellidos:

```php
$cliente->nombreCompleto(); // "Juan García López"
```

Este método está disponible tanto en la entidad como en el DTO `ClienteResponse`.

## Migración

**Archivo:** `migrations/Version20260201000006.php`

Crea la tabla `cliente` con:
- Campos: id, nombre, apellidos, dni_nie, email, telefono, activo
- Timestamps: created_at, updated_at, deleted_at
- Auditoría: created_by, updated_by, deleted_by (FK a usuario)
- Índices en: dni_nie, email, nombre, apellidos, activo, deleted_at

**Ejecutar migración:**
```bash
php bin/console doctrine:migrations:migrate
```

## Características

1. **Validación de DNI/NIE español** con letra de control
2. **Validación de Email** según RFC 5322
3. **Validación de Teléfono** flexible con formato internacional
4. **Soft Delete**: Los clientes no se eliminan físicamente
5. **Auditoría**: Registra quién crea, modifica y elimina
6. **Prevención de duplicados**: DNI/NIE y Email únicos
7. **Búsqueda flexible**: Por nombre, apellidos o ambos
8. **Filtros**: Por estado activo/inactivo
9. **CQRS**: Separación clara entre comandos y consultas
10. **Eventos de dominio**: ClienteCreated, ClienteUpdated, ClienteDeleted
