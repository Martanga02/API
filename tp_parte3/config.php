<?php
// Configuración de la conexión a PostgreSQL.
// Orden de prioridad:
//   1) DATABASE_URL (formato postgres://usuario:clave@host:puerto/base), que es
//      lo que entrega Render para su base de datos administrada.
//   2) Variables sueltas DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS
//      (seteadas en docker-compose.yml o en el entorno).
//   3) Valores por defecto (útil si corrés sin Docker).
$databaseUrl = getenv('DATABASE_URL');
$urlParts = $databaseUrl ? parse_url($databaseUrl) : false;
$urlParts = is_array($urlParts) ? $urlParts : [];

define('DB_HOST', $urlParts['host'] ?? (getenv('DB_HOST') ?: 'db'));
define('DB_PORT', $urlParts['port'] ?? (getenv('DB_PORT') ?: '5432'));
define('DB_NAME', isset($urlParts['path']) ? ltrim($urlParts['path'], '/') : (getenv('DB_NAME') ?: 'peliculas_db'));
define('DB_USER', isset($urlParts['user']) ? rawurldecode($urlParts['user']) : (getenv('DB_USER') ?: 'postgres'));
define('DB_PASS', isset($urlParts['pass']) ? rawurldecode($urlParts['pass']) : (getenv('DB_PASS') ?: 'postgres'));
// "prefer" usa SSL si el servidor lo ofrece. Para una conexión externa a Render
// (fuera de su red interna) puede hacer falta DB_SSLMODE=require.
define('DB_SSLMODE', getenv('DB_SSLMODE') ?: 'prefer');

if (!function_exists('db_connect')) {
    // Abre una conexión PDO a PostgreSQL. $dbName permite apuntar a otra base
    // (los tests de integración usan una base aparte).
    function db_connect(?string $dbName = null): PDO {
        $dsn = 'pgsql:host=' . DB_HOST . ';port=' . DB_PORT
             . ';dbname=' . ($dbName ?? DB_NAME) . ';sslmode=' . DB_SSLMODE;
        return new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    }
}
