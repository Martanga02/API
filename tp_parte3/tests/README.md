# Tests

Scaffold de PHPUnit para este proyecto. Dos suites, cada una con un test de
ejemplo — copiá el patrón correspondiente al agregar más.

- `tests/Unit/` — sin base de datos. Ver `CategoryApiControllerTest.php`: se
  mockea `CategoriesModel` y se testea solo la lógica propia del controller
  (acá, el manejo del 404 en `getCategory` cuando la categoría no existe).
  Por eso `CategoryApiController` recibe un argumento opcional
  `?CategoriesModel $model` en el constructor — aplicá el mismo cambio a
  otros controllers si agregás tests unitarios para ellos.
- `tests/Integration/` — usa una base de datos MySQL real. Ver
  `CategoriesModelIntegrationTest.php`. Corre contra un schema dedicado
  `peliculas_db_test` (que se crea y se llena automáticamente a partir de
  `database/peliculas_db.sql` la primera vez que se ejecuta), en el mismo
  servidor MySQL que ya levanta el servicio `db` de docker-compose. Nunca
  toca el schema real `peliculas_db`, y cada test hace rollback de su propia
  transacción en `tearDown()` para no dejar datos entre corridas.

  Los modelos reciben un argumento opcional `?PDO $db` en el constructor
  (ver `app/models/categories.model.php`) para que los tests puedan inyectar
  una conexión a la base de test en vez de la real. Aplicá el mismo cambio a
  `UserModel`/`PeliculaModel` si agregás tests de integración para ellos
  (`tests/Integration/UserModelIntegrationTest.php` ya tiene el armado, falta
  implementarlo).

## Cómo correrlos

Desde la raíz del proyecto, con el stack levantado (`docker compose up -d`):

```sh
# reconstruir una vez después de bajar estos cambios, para que vendor/ (PHPUnit) exista en la imagen
docker compose build api

# rápidos, sin base de datos
docker compose exec api vendor/bin/phpunit --testsuite Unit

# usan el contenedor real de la db (schema dedicado peliculas_db_test)
docker compose exec api vendor/bin/phpunit --testsuite Integration

# ambas
docker compose exec api vendor/bin/phpunit
```

## Local (sin Docker)

Con PHP y Composer instalados localmente:

```sh
composer install
vendor/bin/phpunit --testsuite Unit
```

La suite `Integration` necesita una base MySQL alcanzable en `DB_HOST`/`DB_USER`/
`DB_PASS`/`DB_NAME` (ver `config.php`). El servicio `db` de docker-compose no
expone el puerto 3306 al host, así que por defecto no hay una DB accesible
localmente para esa suite — para correrla fuera de Docker hay que exponer ese
puerto o apuntar a un MySQL local propio.
