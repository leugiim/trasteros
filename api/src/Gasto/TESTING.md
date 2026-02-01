# Testing del Módulo Gasto

## Casos de Prueba para API REST

### 1. Crear un Gasto (POST /api/gastos)

#### Caso exitoso
```bash
curl -X POST http://localhost:8000/api/gastos \
  -H "Content-Type: application/json" \
  -d '{
    "localId": 1,
    "concepto": "Luz enero 2024",
    "descripcion": "Recibo mensual electricidad",
    "importe": 125.50,
    "fecha": "2024-01-15",
    "categoria": "suministros",
    "metodoPago": "domiciliacion"
  }'
```

Esperado: 201 Created

#### Validaciones a probar

1. **Local inexistente**
```bash
curl -X POST http://localhost:8000/api/gastos \
  -H "Content-Type: application/json" \
  -d '{
    "localId": 999,
    "concepto": "Test",
    "importe": 100,
    "fecha": "2024-01-15",
    "categoria": "otros"
  }'
```
Esperado: 400 Bad Request - "Local with id 999 not found"

2. **Importe negativo**
```bash
curl -X POST http://localhost:8000/api/gastos \
  -H "Content-Type: application/json" \
  -d '{
    "localId": 1,
    "concepto": "Test",
    "importe": -50,
    "fecha": "2024-01-15",
    "categoria": "otros"
  }'
```
Esperado: 400 Bad Request - "Importe cannot be negative"

3. **Categoría inválida**
```bash
curl -X POST http://localhost:8000/api/gastos \
  -H "Content-Type: application/json" \
  -d '{
    "localId": 1,
    "concepto": "Test",
    "importe": 100,
    "fecha": "2024-01-15",
    "categoria": "categoria_invalida"
  }'
```
Esperado: 400 Bad Request - "Invalid gasto categoria"

4. **Método de pago inválido**
```bash
curl -X POST http://localhost:8000/api/gastos \
  -H "Content-Type: application/json" \
  -d '{
    "localId": 1,
    "concepto": "Test",
    "importe": 100,
    "fecha": "2024-01-15",
    "categoria": "otros",
    "metodoPago": "paypal"
  }'
```
Esperado: 400 Bad Request - "Invalid metodo pago"

5. **Fecha inválida**
```bash
curl -X POST http://localhost:8000/api/gastos \
  -H "Content-Type: application/json" \
  -d '{
    "localId": 1,
    "concepto": "Test",
    "importe": 100,
    "fecha": "2024-13-40",
    "categoria": "otros"
  }'
```
Esperado: 400 Bad Request - "Invalid date format"

6. **Campos obligatorios vacíos**
```bash
curl -X POST http://localhost:8000/api/gastos \
  -H "Content-Type: application/json" \
  -d '{
    "localId": 1
  }'
```
Esperado: 400 Bad Request con múltiples errores de validación

### 2. Listar Gastos (GET /api/gastos)

#### Sin filtros
```bash
curl http://localhost:8000/api/gastos
```

#### Filtro por local
```bash
curl http://localhost:8000/api/gastos?localId=1
```

#### Filtro por categoría
```bash
curl http://localhost:8000/api/gastos?categoria=suministros
```

#### Filtro por rango de fechas
```bash
curl "http://localhost:8000/api/gastos?desde=2024-01-01&hasta=2024-12-31"
```

#### Filtros combinados (local + fechas)
```bash
curl "http://localhost:8000/api/gastos?localId=1&desde=2024-01-01&hasta=2024-12-31"
```

#### Solo gastos activos (no eliminados)
```bash
curl http://localhost:8000/api/gastos?onlyActive=true
```

### 3. Obtener un Gasto (GET /api/gastos/{id})

#### Caso exitoso
```bash
curl http://localhost:8000/api/gastos/1
```
Esperado: 200 OK

#### Gasto inexistente
```bash
curl http://localhost:8000/api/gastos/999
```
Esperado: 404 Not Found

### 4. Actualizar un Gasto (PUT /api/gastos/{id})

#### Caso exitoso
```bash
curl -X PUT http://localhost:8000/api/gastos/1 \
  -H "Content-Type: application/json" \
  -d '{
    "localId": 1,
    "concepto": "Luz enero 2024 - Actualizado",
    "descripcion": "Recibo mensual electricidad modificado",
    "importe": 130.00,
    "fecha": "2024-01-15",
    "categoria": "suministros",
    "metodoPago": "transferencia"
  }'
```
Esperado: 200 OK

#### Gasto inexistente
```bash
curl -X PUT http://localhost:8000/api/gastos/999 \
  -H "Content-Type: application/json" \
  -d '{
    "localId": 1,
    "concepto": "Test",
    "importe": 100,
    "fecha": "2024-01-15",
    "categoria": "otros"
  }'
```
Esperado: 404 Not Found

### 5. Eliminar un Gasto (DELETE /api/gastos/{id})

#### Caso exitoso
```bash
curl -X DELETE http://localhost:8000/api/gastos/1
```
Esperado: 204 No Content

#### Gasto inexistente
```bash
curl -X DELETE http://localhost:8000/api/gastos/999
```
Esperado: 404 Not Found

## Pruebas de Integración con PHPUnit

### Ejemplo de Test para CreateGastoCommandHandler

```php
<?php

declare(strict_types=1);

namespace App\Tests\Gasto\Application\Command\CreateGasto;

use App\Gasto\Application\Command\CreateGasto\CreateGastoCommand;
use App\Gasto\Application\Command\CreateGasto\CreateGastoCommandHandler;
use App\Gasto\Domain\Repository\GastoRepositoryInterface;
use App\Local\Domain\Model\Local;
use App\Local\Domain\Model\LocalId;
use App\Local\Domain\Repository\LocalRepositoryInterface;
use PHPUnit\Framework\TestCase;

final class CreateGastoCommandHandlerTest extends TestCase
{
    public function testItCreatesAGasto(): void
    {
        // Arrange
        $localRepository = $this->createMock(LocalRepositoryInterface::class);
        $gastoRepository = $this->createMock(GastoRepositoryInterface::class);

        $local = $this->createMock(Local::class);
        $localRepository
            ->expects($this->once())
            ->method('findById')
            ->with(LocalId::fromInt(1))
            ->willReturn($local);

        $gastoRepository
            ->expects($this->once())
            ->method('save');

        $handler = new CreateGastoCommandHandler($gastoRepository, $localRepository);

        $command = new CreateGastoCommand(
            localId: 1,
            concepto: 'Test Gasto',
            importe: 100.0,
            fecha: '2024-01-15',
            categoria: 'otros'
        );

        // Act
        $response = $handler($command);

        // Assert
        $this->assertNotNull($response);
        $this->assertEquals('Test Gasto', $response->concepto);
        $this->assertEquals(100.0, $response->importe);
    }
}
```

## Verificar Configuración

### 1. Verificar que el repositorio está registrado
```bash
php bin/console debug:container GastoRepositoryInterface
```

### 2. Verificar rutas
```bash
php bin/console debug:router | grep gasto
```

Deberías ver:
```
gastos_list      GET      /api/gastos
gastos_show      GET      /api/gastos/{id}
gastos_create    POST     /api/gastos
gastos_update    PUT      /api/gastos/{id}
gastos_delete    DELETE   /api/gastos/{id}
```

### 3. Ejecutar migraciones
```bash
php bin/console doctrine:migrations:migrate
```

### 4. Verificar entidad
```bash
php bin/console doctrine:mapping:info
```

## Datos de Prueba SQL

```sql
-- Insertar un local de prueba (si no existe)
INSERT INTO local (nombre, direccion_id, created_at, updated_at)
VALUES ('Local Test', 1, NOW(), NOW());

-- Insertar gastos de prueba
INSERT INTO gasto (local_id, concepto, descripcion, importe, fecha, categoria, metodo_pago, created_at, updated_at)
VALUES
  (1, 'Luz enero 2024', 'Recibo electricidad', 125.50, '2024-01-15', 'suministros', 'domiciliacion', NOW(), NOW()),
  (1, 'IBI 2024', 'Impuesto bienes inmuebles', 450.00, '2024-01-10', 'impuestos', 'transferencia', NOW(), NOW()),
  (1, 'Seguro hogar', 'Prima anual seguro', 350.00, '2024-01-05', 'seguros', 'transferencia', NOW(), NOW()),
  (1, 'Reparación puerta', 'Cambio cerradura', 85.00, '2024-01-20', 'mantenimiento', 'efectivo', NOW(), NOW());

-- Consultar totales
SELECT
  l.nombre,
  COUNT(g.id) as total_gastos,
  SUM(g.importe) as total_importe
FROM local l
LEFT JOIN gasto g ON g.local_id = l.id AND g.deleted_at IS NULL
GROUP BY l.id, l.nombre;

-- Gastos por categoría
SELECT
  categoria,
  COUNT(*) as cantidad,
  SUM(importe) as total
FROM gasto
WHERE deleted_at IS NULL
GROUP BY categoria
ORDER BY total DESC;
```
