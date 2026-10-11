<?php

// Opens a PDO connection to a dedicated `peliculas_db_test` schema on the
// same MySQL server used by docker-compose (see config.php / DB_HOST),
// creating and seeding it from database/peliculas_db.sql on first run.
// Never touches the real `peliculas_db` schema.
function integration_test_pdo(): PDO
{
    $testDbName = getenv('DB_TEST_NAME') ?: 'peliculas_db_test';

    $setup = new PDO('mysql:host=' . DB_HOST . ';charset=utf8mb4', DB_USER, DB_PASS);
    $setup->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $setup->exec("CREATE DATABASE IF NOT EXISTS `$testDbName`");
    $setup = null;

    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . $testDbName . ';charset=utf8mb4', DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $alreadySeeded = $pdo->query("SHOW TABLES LIKE 'genero'")->fetch();
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
        // Skip chunks that are only "-- ..." comment lines (no real SQL in them).
        if (trim(preg_replace('/^--.*$/m', '', $statement)) === '') {
            continue;
        }
        $pdo->exec($statement);
    }
}
