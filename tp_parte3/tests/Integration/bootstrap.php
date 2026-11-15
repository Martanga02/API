<?php

// Abre una conexión PDO a una base dedicada `peliculas_db_test` en el mismo
// servidor PostgreSQL que usa docker-compose (ver config.php / DB_HOST),
// creándola y cargándola desde database/peliculas_db.sql la primera vez.
// Nunca toca la base real `peliculas_db`.
function integration_test_pdo(): PDO
{
    $testDbName = getenv('DB_TEST_NAME') ?: 'peliculas_db_test';

    // Para crear una base hay que conectarse a otra que ya exista: `postgres`
    // es la base de mantenimiento que PostgreSQL trae siempre.
    $setup = db_connect('postgres');
    $exists = $setup->prepare('SELECT 1 FROM pg_database WHERE datname = ?');
    $exists->execute([$testDbName]);
    if (!$exists->fetchColumn()) {
        // El nombre sale de una variable de entorno nuestra; se escapa igual por prolijidad.
        $setup->exec('CREATE DATABASE "' . str_replace('"', '""', $testDbName) . '"');
    }
    $setup = null;

    $pdo = db_connect($testDbName);

    // to_regclass devuelve NULL si la tabla no existe: en ese caso hay que cargar el script.
    $alreadySeeded = $pdo->query("SELECT to_regclass('public.genero')")->fetchColumn();
    if (!$alreadySeeded) {
        seed_integration_test_db($pdo);
    }

    return $pdo;
}

function seed_integration_test_db(PDO $pdo): void
{
    $dump = file_get_contents(__DIR__ . '/../../database/peliculas_db.sql');

    foreach (explode(';', $dump) as $statement) {
        $statement = trim($statement);
        if ($statement === '') {
            continue;
        }
        // Saltea los pedazos que son solo comentarios "-- ..." (sin SQL real).
        if (trim(preg_replace('/^--.*$/m', '', $statement)) === '') {
            continue;
        }
        $pdo->exec($statement);
    }
}
