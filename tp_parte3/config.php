<?php
// Si existe la variable de entorno la usa (seteada en docker-compose.yml),
// si no, cae en el valor por defecto (útil si corrés sin Docker).
define('DB_HOST', getenv('DB_HOST') ?: 'db');
define('DB_NAME', getenv('DB_NAME') ?: 'peliculas_db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: 'root');