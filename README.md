# Cartelera: API REST (PHP) y cómo levantar todo el proyecto

[![Build](https://github.com/Martanga02/API/actions/workflows/build.yml/badge.svg)](https://github.com/Martanga02/API/actions/workflows/build.yml)

Aplicación de películas y categorías formada por tres servicios:

| Servicio | Tecnología | Puerto | Imagen |
|---|---|---|---|
| **Frontend** | React + Vite, servido con nginx | 5173 | `martinaflorestosini/cartelera-frontend` (propia) |
| **Backend (API)** | PHP 8.2 + Apache | 8081 | `martinaflorestosini/cartelera-api` (propia) |
| **Base de datos** | PostgreSQL 16 | (interno) | `postgres:16-alpine` (oficial, se inicializa con `peliculas_db.sql`) |

Además se levanta **Adminer** (puerto 8082), una página web para ver la base de datos. Es opcional.

El proyecto está en dos repositorios:

- Backend: https://github.com/Martanga02/API
- Frontend: https://github.com/Martanga02/FRONTEND-API

## Versión desplegada en Render

| Servicio | Dirección |
|---|---|
| API | https://cartelera-api-v72e.onrender.com/api/categoria |

La base es un PostgreSQL administrado de Render, y los dos servicios web corren las imágenes de Docker Hub. Las instancias gratuitas se duermen tras 15 minutos sin tráfico: el primer pedido puede tardar cerca de un minuto.

## Requisitos

- **Docker** con **Docker Compose** (en Windows y Mac, Docker Desktop; abrirlo antes de empezar).
- **Git** (o descargar los repos como zip).
- Puertos libres: `5173`, `8081` y `8082`.

Se comprueba con:

```bash
docker --version
docker compose version
```

## Opción A: levantar desde el código (construye las imágenes)

### 1. Descargar los dos repositorios

Los dos tienen que quedar en una misma carpeta:

```bash
mkdir tp
cd tp
git clone https://github.com/Martanga02/API.git API-main
git clone https://github.com/Martanga02/FRONTEND-API.git FRONTEND-API-main
```

### 2. Levantar el backend (api + base de datos + adminer)

```bash
cd API-main
docker compose up -d --build
docker compose ps
```

La primera vez tarda varios minutos (descarga imágenes y construye). Esperar a que `docker compose ps` muestre `api`, `db` y `adminer` corriendo.

> **Si ya levantaste una versión anterior de este proyecto (con MySQL),** antes corré `docker compose down -v` en esta carpeta. El `-v` borra el volumen viejo de la base; si no, PostgreSQL no puede arrancar sobre archivos de MySQL.

### 3. Verificar que el frontend apunte a la API

En el repo del frontend, el archivo `docker-compose.yml` tiene que tener esta línea:

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
| App | http://localhost:5173 |
| API (lista de categorías) | http://localhost:8081/api/categoria |
| Health check | http://localhost:8081/api/healthcheck |
| Adminer | http://localhost:8082 |

> **Escribir el `http://` completo.** Algunos navegadores completan `localhost` con `https://`, y el servidor solo habla HTTP (error `ERR_SSL_PROTOCOL_ERROR`).

**Usuarios de la aplicación** (cargados por el script de la base):

| Usuario | Contraseña | Rol | Permisos |
|---|---|---|---|
| `admin` | `admin123` | ADMIN | Puede crear categorías |
| `demo` | `demo123` | USER | Solo lectura (si intenta crear, la API responde 403) |

**Adminer** (servidor de la base de datos, no es el login de la app):

| Campo | Valor |
|---|---|
| Motor | PostgreSQL |
| Servidor | `db` |
| Usuario | `postgres` |
| Contraseña | `postgres` |
| Base de datos | `peliculas_db` |

## Opción B: levantar con las imágenes de Docker Hub (sin construir)

Las imágenes ya están publicadas:

- https://hub.docker.com/r/martinaflorestosini/cartelera-api
- https://hub.docker.com/r/martinaflorestosini/cartelera-frontend

Hace falta el repo del backend (el compose monta el script SQL de la base desde ahí). Desde su carpeta raíz:

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

> La imagen del frontend publicada en Docker Hub se construye con la URL de la API de Render (variable del repo `VITE_API_BASE_URL`), así que **contra `localhost` solo funciona la Opción A**.

## Opción C: Kubernetes local

La carpeta `k8s/` tiene los manifiestos (ReplicaSet, Service NodePort y HPA para la API; ReplicaSet y Service para el frontend; Deployment y volumen para PostgreSQL). Ver `k8s/README.md`.

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

## Tests

**Backend (PHPUnit):** con el stack del backend levantado,

```bash
cd API-main
docker compose exec api vendor/bin/phpunit
```

Hay 7 tests unitarios (con mocks, sin base de datos) y 2 de integración (contra PostgreSQL, en una base aparte `peliculas_db_test` y con rollback por test). Ver `tp_parte3/tests/README.md`.

**Frontend (Vitest + Testing Library):** requiere Node 20.

```bash
cd FRONTEND-API-main
npm install
npm test
```

Hay 13 tests sobre el cliente de la API, el contexto de autenticación y las rutas protegidas. Con coverage: `npm run test:coverage`.

## Integración continua y despliegue (GitHub Actions)

**Workflow `Build`** (`.github/workflows/build.yml`), en cada push a `main`, en cada tag `x.y.z` y en cada pull request:

1. **test:** PHPUnit (unitarios + integración contra PostgreSQL) con **coverage** (driver pcov). Los reportes quedan como artefacto del run y, si existe el secret `CODECOV_TOKEN`, se suben a Codecov.
2. **quality:** chequeo de sintaxis de PHP (bloqueante) y análisis estático con PHPStan (informativo).
3. **docker:** construye la imagen (`linux/amd64`). En un pull request solo se valida; en push a `main` se publica en Docker Hub con los tags `main`, `latest` y `sha-<commit completo>`. No se publica nada si los tests fallan.

**Workflow `Deploy Render`** (`.github/workflows/deploy-render.yml`): se lanza a mano (Actions, Deploy Render, Run workflow). Hace un GET al deploy hook de Render: sin tag, Render vuelve a bajar la imagen configurada en el servicio (`latest`); con un tag opcional (`sha-<commit>`) pide esa versión.

**Secrets del repo** (Settings, Secrets and variables, Actions): `DOCKERHUB_USERNAME`, `DOCKERHUB_TOKEN` y `DEPLOY_URL` (el deploy hook del servicio de Render). Opcional: `CODECOV_TOKEN`. Sin los secrets de Docker Hub, el workflow construye la imagen y muestra un aviso, sin fallar.

**Dependencias:** `.github/dependabot.yml` abre pull requests semanales con versiones nuevas (Composer, GitHub Actions y Docker).

## Publicar las imágenes en Docker Hub (manual)

Lo normal es que las publique el workflow `Build`. A mano, con la sesión iniciada en Docker Hub (`docker login`, o el botón *Sign in* de Docker Desktop):

```bash
cd API-main
docker build -t martinaflorestosini/cartelera-api:latest .
docker push martinaflorestosini/cartelera-api:latest

cd ../FRONTEND-API-main
docker build --build-arg VITE_API_BASE_URL=http://localhost:8081/api -t martinaflorestosini/cartelera-frontend:latest .
docker push martinaflorestosini/cartelera-frontend:latest
```

El nombre de usuario va en minúsculas. En `docker login`, como contraseña se puede usar un token de acceso (Docker Hub, Account Settings, Personal access tokens).

## Problemas frecuentes

| Síntoma | Causa y solución |
|---|---|
| `ERR_SSL_PROTOCOL_ERROR` al abrir `localhost:5173` | El navegador fuerza HTTPS. Escribir `http://localhost:5173` completo, o probar en una ventana de incógnito. |
| `port is already allocated` | Otro programa usa 5173, 8081 u 8082. Cerrarlo o cambiar el puerto en el compose. |
| `Cannot connect to the Docker daemon` | Docker no está corriendo. Abrir Docker Desktop (o iniciar el servicio en Linux). |
| En Linux, `permission denied` al usar Docker | Anteponer `sudo` a los comandos, o agregar el usuario al grupo `docker`. |
| El front carga pero no trae datos ni deja loguear | El front tiene una URL de API incorrecta. Revisar `VITE_API_BASE_URL` (debe ser `http://localhost:8081/api`), reconstruir con `docker compose up -d --build` y recargar con `Ctrl + Shift + R`. |
| El front no muestra datos al recién levantar | El backend todavía está iniciando. Ver `docker compose logs api` en la carpeta del backend. |
| `PDOException: could not find driver` | La imagen de la API no tiene el driver de PostgreSQL (`pdo_pgsql`): se construyó con un `Dockerfile` viejo. Reconstruir con `--build`. |
| La API muestra `Forbidden` en la raíz (`/`) | Es normal: la API solo responde bajo `/api/...`. Probar `/api/categoria`. |
| Adminer dice `Access denied` | Elegir motor PostgreSQL y usar servidor `db`, usuario `postgres`, contraseña `postgres` (no los usuarios de la app). |

---

# Categorias REST API

Este repositorio contiene una API REST simple para gestionar categorías de películas (géneros).

## Qué hay en este proyecto

* `api_router.php` - Entry point para los endpoints de la API.
* `app/controllers/` - Controladores, por ejemplo `categories-api.controller.php`, `auth-api.controller.php`, `health-api.controller.php`.
* `app/models/` - Modelos, por ejemplo `categories.model.php`, `user.model.php`.
* `app/middlewares/` - Middlewares utilizados por la API (JWT y Guard).
* `libs/router/` - Librería ligera de ruteo usada por este proyecto.
* `libs/jwt/` - Librería para manejar JWT.
* `config.php` - Configuración de la conexión a PostgreSQL (variables `DB_*` o `DATABASE_URL`).
* `database/peliculas_db.sql` - Script SQL para crear la base de datos (PostgreSQL).
* `.htaccess` - Reglas Apache para soportar URL semánticas.
* `k8s/` - Manifiestos de Kubernetes para correr todo en un cluster local.

## Librería de ruteo

Este proyecto usa una librería interna para rutear peticiones ubicada en `libs/router/`.
Consulta la documentación de la librería de ruteo aquí:

`libs/router/README.md`

## Endpoints

### Salud

`GET /healthcheck` — responde `{"status":"ok"}` sin usar la base de datos. Lo usan Kubernetes y Render para saber si la API está viva.

### Autenticación

`GET /auth/login` — obtener un token JWT utilizando Basic Auth.

### Categorías (público)

`GET /categoria` — listar categorías.

Permite filtrado opcional:

`GET /categoria?nombre=texto`

`GET /categoria/:id` — ver una categoría por ID.

### Categorías (protegido, requiere JWT)

`POST /categoria` — crear una nueva categoría. Requiere header: `Authorization: Bearer <token>`

```php
$router->addMiddleware(new GuardMiddleware());
$router->addRoute('categoria', 'POST', 'CategoryApiController', 'insertCategory');
```

