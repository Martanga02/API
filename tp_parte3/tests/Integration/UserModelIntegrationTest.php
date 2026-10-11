<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../../app/models/user.model.php';

// TODO(alumno): implementar este test.
//
// Objetivo: verificar que UserModel::getByUser() encuentra al usuario admin
// sembrado (email 'admin@admin.com', ver database/peliculas_db.sql) contra
// una base de datos real.
//
// Pasos:
//   1. UserModel todavía no acepta un PDO inyectado. Agregá el mismo
//      parámetro `?PDO $db = null` en el constructor que ya tiene
//      CategoriesModel (ver app/models/categories.model.php) para que
//      $this->model corra contra la base de test en vez de la real.
//   2. Llamá a $this->model->getByUser(...) con el identificador del admin
//      sembrado y verificá que la fila devuelta sea la esperada.
//   3. Corré esta suite (ver tests/README.md) y seguí el error que te
//      devuelve — algo en UserModel no coincide con el schema real de la DB.
class UserModelIntegrationTest extends TestCase
{
    private PDO $db;
    private UserModel $model;

    protected function setUp(): void
    {
        $this->db = integration_test_pdo();
        $this->db->beginTransaction();
        $this->model = new UserModel($this->db);
    }

    protected function tearDown(): void
    {
        $this->db->rollBack();
    }

    public function testGetByUserReturnsSeededAdmin(): void
    {
        $user = $this->model->getByUser('admin');
        $this->assertNotFalse($user);
        $this->assertSame('ADMIN',$user->rol);
    }
}
