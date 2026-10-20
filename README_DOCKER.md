# Cartelera: cómo levantar el proyecto con Docker

Aplicación de películas y categorías formada por tres servicios:

| Servicio | Tecnología | Puerto | Imagen |
|---|---|---|---|
| **Frontend** | React + Vite, servido con nginx | 5173 | `martinaflorestosini/cartelera-frontend` (propia) |
| **Backend (API)** | PHP 8.2 + Apache | 8081 | `martinaflorestosinio/cartelera-api` (propia) |
| **Base de datos** | MySQL 8.0 | (interno) | `mysql:8.0` (oficial, se inicializa con `peliculas_db.sql`) |

Además se levanta **Adminer** (puerto 8082), una página web para ver la base de datos. Es opcional.

El proyecto está en dos repositorios:

- Backend: [`https://github.com/<usuario>/<repo-backend>`](https://github.com/Martanga02/API)
- Frontend: [`https://github.com/<usuario>/<repo-frontend>`](https://github.com/Martanga02/FRONTEND-API)

---

## Requisitos

- **Docker** con **Docker Compose** (en Windows y Mac, Docker Desktop; abrirlo antes de empezar).
- **Git** (o descargar los repos como zip).
- Puertos libres: `5173`, `8081` y `8082`.

Se comprueba con:

```bash
docker --version
docker compose version
```

---

## Opción A: levantar desde el código (construye las imágenes)

### 1. Descargar los dos repositorios

Los dos tienen que quedar en una misma carpeta:

```bash
mkdir tp
cd tp
git clone https://github.com/<usuario>/<repo-backend>.git API-main
git clone https://github.com/<usuario>/<repo-frontend>.git FRONTEND-API-main
```

### 2. Levantar el backend (api + base de datos + adminer)

```bash
cd API-main
docker compose up -d --build
docker compose ps
```

La primera vez tarda varios minutos (descarga imágenes y construye). Esperar a que `docker compose ps` muestre `api`, `db` y `adminer` corriendo.

### 3. Verificar que el frontend apunte a la API

En el **repo del frontend**, el archivo `docker-compose.yml` tiene que tener esta línea:

```yaml
VITE_API_BASE_URL: http://localhost:8081/api
```

Dentro de Docker, la API responde en `/api` (no en `/tp_parte3/api`, que es la ruta de XAMPP). Esta URL se fija **al construir** la imagen del front: si se cambia, hay que reconstruir con `--build`.

### 4. Levantar el frontend

```bash
cd ../FRONTEND-API-main
docker compose up -d --build
docker compose ps
```

### 5. Probar

| Qué | Dirección |
|---|---|
| App | `http://localhost:5173` |
| API (lista de categorías) | `http://localhost:8081/api/categoria` |
| Adminer | `http://localhost:8082` |

> **Escribir el `http://` completo.** Algunos navegadores completan `localhost` con `https://`, y el servidor solo habla HTTP (error `ERR_SSL_PROTOCOL_ERROR`).

**Usuarios de la aplicación** (cargados por el script de la base):

| Usuario | Contraseña | Rol | Permisos |
|---|---|---|---|
| `admin` | `admin123` | ADMIN | Puede crear categorías |
| `demo` | `demo123` | USER | Solo lectura (si intenta crear, la API responde 403) |

**Adminer** (servidor de la base de datos, no es el login de la app):

| Campo | Valor |
|---|---|
| Motor | MySQL / MariaDB |
| Servidor | `db` |
| Usuario | `root` |
| Contraseña | `root` |
| Base de datos | `peliculas_db` |

---

## Opción B: levantar con las imágenes de Docker Hub (sin construir)

Las imágenes ya están publicadas:

- `https://hub.docker.com/r/martinaflorestosini/cartelera-api`
- `https://hub.docker.com/r/martinaflorestosini/cartelera-frontend`

Hace falta el repo del **backend** (el compose monta el script SQL de la base desde ahí). Desde su carpeta raíz:

**Linux / Mac:**

```bash
cd API-main
DOCKERHUB_USER=martinaflorestosini docker compose -f docker-compose.hub.yml up -d
```

**Windows (PowerShell):**

```powershell
cd API-main
$env:DOCKERHUB_USER="martinaflorestosini"
docker compose -f docker-compose.hub.yml up -d
```

Se prueba en las mismas direcciones y con los mismos usuarios de la opción A.

---

## Frenar los contenedores

Desde la carpeta de cada proyecto (el backend y el frontend son stacks separados):

```bash
cd API-main
docker compose down

cd ../FRONTEND-API-main
docker compose down
```

Si se levantó con el compose de Docker Hub: `docker compose -f docker-compose.hub.yml down`.

- `down` conserva los datos de la base (volumen `db_data`).
- `docker compose down -v` borra también los datos; la próxima vez la base se recrea desde el script SQL.
- `docker compose stop` / `docker compose start` pausan y retoman sin eliminar nada.

---

## Tests

**Backend** (PHPUnit): con el stack del backend levantado,

```bash
cd API-main
docker compose exec api vendor/bin/phpunit
```

Hay 6 tests unitarios (con mocks, sin base de datos) y 2 de integración (contra MySQL, en un esquema aparte `peliculas_db_test` y con rollback por test).

**Frontend** (Vitest + Testing Library): requiere Node 20.

```bash
cd FRONTEND-API-main
npm install
npm test
```

Hay 13 tests sobre el cliente de la API, el contexto de autenticación y las rutas protegidas.

---

## Integración continua (GitHub Actions)

Cada repo tiene un workflow en `.github/workflows/ci.yml` que se ejecuta en cada push a `main` y en cada pull request:

- **Backend:** levanta un MySQL de prueba, instala PHP 8.2 y Composer, y corre los tests unitarios y de integración.
- **Frontend:** instala dependencias, corre los tests y verifica que el build de producción compile.

El resultado se ve en la pestaña **Actions** de cada repositorio.

---

## Publicar las imágenes en Docker Hub (manual)

Con la sesión iniciada en Docker Hub (`docker login`, o el botón *Sign in* de Docker Desktop):

```bash
cd API-main
docker build -t martinaflorestosini/cartelera-api:latest .
docker push martinaflorestosini/cartelera-api:latest

cd ../FRONTEND-API-main
docker build --build-arg VITE_API_BASE_URL=http://localhost:8081/api -t martinaflorestosini/cartelera-frontend:latest .
docker push martinaflorestosini/cartelera-frontend:latest
```

El nombre de usuario va en minúsculas. En `docker login`, como contraseña se puede usar un token de acceso (Docker Hub → Account Settings → Personal access tokens).

---

## Problemas frecuentes

| Síntoma | Causa y solución |
|---|---|
| `ERR_SSL_PROTOCOL_ERROR` al abrir `localhost:5173` | El navegador fuerza HTTPS. Escribir `http://localhost:5173` completo, o probar en una ventana de incógnito. |
| `port is already allocated` | Otro programa usa 5173, 8081 u 8082. Cerrarlo o cambiar el puerto en el compose. |
| `Cannot connect to the Docker daemon` | Docker no está corriendo. Abrir Docker Desktop (o iniciar el servicio en Linux). |
| En Linux, `permission denied` al usar Docker | Anteponer `sudo` a los comandos, o agregar el usuario al grupo `docker`. |
| El front carga pero no trae datos ni deja loguear | El front tiene una URL de API incorrecta. Revisar `VITE_API_BASE_URL` (debe ser `http://localhost:8081/api`), reconstruir con `docker compose up -d --build` y recargar con `Ctrl + Shift + R`. |
| El front no muestra datos al recién levantar | El backend todavía está iniciando. Ver `docker compose logs api` en la carpeta del backend. |
| Adminer dice `Access denied` | Usar servidor `db`, usuario `root`, contraseña `root` (no los usuarios de la app). |







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

## Tests

El proyecto (dentro de `tp_parte3/`) incluye tests unitarios y de integración con PHPUnit. Ver [tp_parte3/tests/README.md](tp_parte3/tests/README.md) para más detalle.

```sh
# desde la raíz del repo, con el stack levantado (docker compose up -d)
docker compose exec api vendor/bin/phpunit
```

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

