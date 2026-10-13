<?php
// --- CORS ---
// El frontend (Vite/React) vive en otro origen (por ej. http://localhost:5173),
// así que sin estos headers el navegador bloquea las respuestas. También hay
// que responder el preflight OPTIONS que el navegador manda solo cuando ve el
// header Authorization, antes de que el router intente rutearlo (no hay
// ninguna ruta OPTIONS registrada, así que si no cortamos acá el preflight
// termina en un 404 sin headers CORS y el navegador aborta el pedido real).
$allowedOrigin = getenv('CORS_ALLOWED_ORIGIN') ?: '*';
header("Access-Control-Allow-Origin: $allowedOrigin");
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("HTTP/1.1 204 No Content");
    exit;
}

require_once './libs/router/router.php';

require_once './libs/jwt/jwt.middleware.php';

require_once './app/middlewares/guard-api.middleware.php';
require_once './app/controllers/categories-api.controller.php';
require_once 'app/controllers/pelicula.api.controller.php';
require_once './app/controllers/auth-api.controller.php';

// instancio el router
$router = new Router();

// --- Middleware que valida el token JWT (para todos los endpoints) ---
$router->addMiddleware(new JWTMiddleware());

// --- Rutas públicas de categorias ---
$router->addRoute('auth/login', 'GET', 'AuthApiController', 'login');
$router->addRoute('categoria', 'GET', 'CategoryApiController', 'getCategories');
$router->addRoute('categoria/:id', 'GET', 'CategoryApiController', 'getCategory');
// --- Rutas de películas ---
$router->addRoute('peliculas', 'GET', 'PeliculaApiController', 'getAll');
$router->addRoute('peliculas/:id', 'PUT', 'PeliculaApiController', 'update');

// --- Middleware que exige autenticación y rol válido ---
$router->addMiddleware(new GuardMiddleware());

// --- Rutas protegidas ---
$router->addRoute('categoria', 'POST', 'CategoryApiController', 'insertCategory');

// --- Ejecuta el enrutamiento ---
$router->route($_GET["resource"], $_SERVER['REQUEST_METHOD']);