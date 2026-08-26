<?php

declare(strict_types=1);

/**
 * One-off data migration: copies rows from the old Supabase (Postgres)
 * database into the new MariaDB one. Schema is NOT touched here — it's
 * already created by doctrine:migrations:migrate (run automatically by
 * docker-entrypoint.sh). This only moves rows, table by table, in an order
 * that respects foreign keys, and resets each MariaDB AUTO_INCREMENT
 * counter afterwards so future inserts don't collide with migrated ids.
 *
 * Plain PDO on purpose (no Doctrine/composer autoload) so it can run in a
 * throwaway container with just pdo_pgsql + pdo_mysql, without needing
 * vendor/ installed.
 *
 * Usage:
 *   SUPABASE_DATABASE_URL=postgresql://user:pass@host:5432/dbname \
 *   DATABASE_URL=mysql://user:pass@host:3306/dbname \
 *   php migrate-from-supabase.php
 *
 * Safe to re-run: skips any table that already has rows in the target.
 */

function pdoFromPostgresUrl(string $url): PDO
{
    $parts = parse_url($url);
    $dsn = sprintf(
        'pgsql:host=%s;port=%d;dbname=%s',
        $parts['host'],
        $parts['port'] ?? 5432,
        ltrim($parts['path'] ?? '', '/')
    );

    return new PDO($dsn, $parts['user'] ?? null, $parts['pass'] ?? null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
}

function pdoFromMysqlUrl(string $url): PDO
{
    $parts = parse_url($url);
    parse_str($parts['query'] ?? '', $query);
    $charset = $query['charset'] ?? 'utf8mb4';
    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        $parts['host'],
        $parts['port'] ?? 3306,
        ltrim($parts['path'] ?? '', '/'),
        $charset
    );

    return new PDO($dsn, $parts['user'] ?? null, $parts['pass'] ?? null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
}

$sourceUrl = getenv('SUPABASE_DATABASE_URL');
$targetUrl = getenv('DATABASE_URL');

if (!$sourceUrl) {
    fwrite(STDERR, "SUPABASE_DATABASE_URL no está definida.\n");
    exit(1);
}

if (!$targetUrl || !str_starts_with($targetUrl, 'mysql://')) {
    fwrite(STDERR, "DATABASE_URL no está definida o no es una URL mysql://.\n");
    exit(1);
}

$source = pdoFromPostgresUrl($sourceUrl);
$target = pdoFromMysqlUrl($targetUrl);

// Orden que respeta las FKs de la migración (ver migrations/): usuario no
// depende de nadie; direccion/cliente solo de usuario; local de direccion;
// trastero/prestamo de local; contrato de trastero+cliente; gasto de
// local+prestamo; ingreso de contrato. 'id' => tipo de PK ('uuid' no
// necesita reset de AUTO_INCREMENT, 'int' sí).
$tables = [
    'usuario' => 'uuid',
    'direccion' => 'int',
    'local' => 'int',
    'cliente' => 'int',
    'trastero' => 'int',
    'prestamo' => 'int',
    'contrato' => 'int',
    'ingreso' => 'int',
    'gasto' => 'int',
];

$target->exec('SET FOREIGN_KEY_CHECKS=0');

foreach ($tables as $table => $idType) {
    $rows = $source->query("SELECT * FROM {$table}")->fetchAll(PDO::FETCH_ASSOC);
    $sourceCount = count($rows);

    if ($sourceCount === 0) {
        echo "{$table}: 0 filas en origen, nada que migrar\n";
        continue;
    }

    $existing = (int) $target->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
    if ($existing > 0) {
        echo "{$table}: ya tiene {$existing} filas en destino, se salta (¿ya migrado?)\n";
        continue;
    }

    $columns = array_keys($rows[0]);
    $columnList = implode(',', array_map(static fn (string $c) => "`{$c}`", $columns));
    $placeholders = implode(',', array_fill(0, count($columns), '?'));
    $stmt = $target->prepare("INSERT INTO `{$table}` ({$columnList}) VALUES ({$placeholders})");

    $target->beginTransaction();
    foreach ($rows as $row) {
        $values = array_map(static function ($v) {
            // Postgres booleans/PDO driver quirks -> MariaDB TINYINT(1)
            if (is_bool($v)) {
                return (int) $v;
            }
            if ($v === 't') {
                return 1;
            }
            if ($v === 'f') {
                return 0;
            }

            return $v;
        }, array_values($row));
        $stmt->execute($values);
    }
    $target->commit();

    if ($idType === 'int') {
        $maxId = (int) $target->query("SELECT MAX(id) FROM `{$table}`")->fetchColumn();
        $target->exec("ALTER TABLE `{$table}` AUTO_INCREMENT = " . ($maxId + 1));
    }

    $insertedCount = (int) $target->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
    $status = $insertedCount === $sourceCount ? 'OK' : 'DESAJUSTE';
    echo "{$table}: {$sourceCount} en origen -> {$insertedCount} en destino [{$status}]\n";
}

$target->exec('SET FOREIGN_KEY_CHECKS=1');

echo "Migración completa.\n";
