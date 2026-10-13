<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../../app/models/categories.model.php';

// Example integration test: hits a real MySQL database (a dedicated
// `peliculas_db_test` schema, auto-created/seeded from
// database/peliculas_db.sql the first time it runs). Each test runs inside
// a transaction that's rolled back afterwards, so runs stay isolated and
// repeatable. Use this file as the template for testing the other models
// against the real DB.
class CategoriesModelIntegrationTest extends TestCase
{
    private PDO $db;
    private CategoriesModel $model;

    protected function setUp(): void
    {
        $this->db = integration_test_pdo();
        $this->db->beginTransaction();
        $this->model = new CategoriesModel($this->db);
    }

    protected function tearDown(): void
    {
        $this->db->rollBack();
    }

    public function testInsertAndGetRoundTrip(): void
    {
        $id = $this->model->insert('Test genre', 'Created by CategoriesModelIntegrationTest');

        $category = $this->model->get($id);

        $this->assertNotFalse($category);
        $this->assertSame('Test genre', $category->nombre);
        $this->assertSame('Created by CategoriesModelIntegrationTest', $category->descripcion);
    }
}
