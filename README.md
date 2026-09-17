# Categorias REST API

Este repositorio contiene una API REST simple para gestionar categorías de películas (géneros).

## Qué hay en este proyecto

* `api_router.php` - Entry point para los endpoints de la API.
* `app/controllers/` - Controladores, por ejemplo `categories-api.controller.php`, `auth-api.controller.php`.
* `app/models/` - Modelos, por ejemplo `categories.model.php`, `user.model.php`.
* `app/middlewares/` - Middlewares utilizados por la API (JWT y Guard).
* `libs/router/` - Librería ligera de ruteo usada por este proyecto.
* `libs/jwt/` - Librería para manejar JWT.
* `database/peliculas_db.sql` - Script SQL para crear la base de datos.
* `.htaccess` - Reglas Apache para soportar URL semánticas.

## Librería de ruteo

Este proyecto usa una librería interna para rutear peticiones ubicada en `libs/router/`.
Consulta la documentación de la librería de ruteo aquí:

[libs/router/README.md](libs/router/README.md)

## Endpoints

### Autenticación

**GET /auth/login** — obtener un token JWT utilizando *Basic Auth*.

### **Categorías (público)**

GET /categoria — listar categorías.

  * Permite filtrado opcional:

    * `GET /categoria?nombre=texto`

GET /categoria/:id — ver una categoría por ID.

### **Categorías (protegido, requiere JWT)**

POST /categoria — crear una nueva categoría.
  Requiere header:
  `Authorization: Bearer <token>`
$router->addMiddleware(new GuardMiddleware());
$router->addRoute('categoria', 'POST', 'CategoryApiController', 'insertCategory');

---
