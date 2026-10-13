<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../app/controllers/categories-api.controller.php';

// Tests unitarios de CategoryApiController::insertCategory (sin base de datos:
// el modelo se mockea). Verifican la validación de datos y la respuesta 201.
class CategoryInsertApiControllerTest extends TestCase
{
    public function testInsertCategoryReturns400WhenDataIsMissing(): void
    {
        $model = $this->createMock(CategoriesModel::class);
        // Si faltan datos, nunca debe llegar a insertar en la base.
        $model->expects($this->never())->method('insert');

        $controller = new CategoryApiController($model);
        $req = (object) ['body' => (object) ['nombre' => 'Terror']]; // falta descripcion
        $res = new Response();

        $this->expectOutputString(json_encode('Faltan datos'));
        $controller->insertCategory($req, $res);
        $this->assertTrue($res->hasFinished());
    }

    public function testInsertCategoryReturnsTheCreatedCategory(): void
    {
        $created = (object) ['id' => 5, 'nombre' => 'Terror', 'descripcion' => 'Miedo'];

        $model = $this->createMock(CategoriesModel::class);
        $model->expects($this->once())->method('insert')->with('Terror', 'Miedo')->willReturn('5');
        $model->method('get')->with('5')->willReturn($created);

        $controller = new CategoryApiController($model);
        $req = (object) ['body' => (object) ['nombre' => 'Terror', 'descripcion' => 'Miedo']];
        $res = new Response();

        $this->expectOutputString(json_encode($created));
        $controller->insertCategory($req, $res);
        $this->assertTrue($res->hasFinished());
    }
}
