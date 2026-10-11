<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../app/controllers/categories-api.controller.php';

// Test unitario de ejemplo: sin base de datos. El modelo se mockea, así que
// lo único que se ejercita es la lógica propia del controller (acá, el
// manejo del 404 cuando la categoría no existe).
class CategoryApiControllerTest extends TestCase
{
    public function testGetCategoryReturns404WhenCategoryDoesNotExist(): void
    {
        $model = $this->createMock(CategoriesModel::class);
        $model->method('get')->with('99')->willReturn(false);

        $controller = new CategoryApiController($model);
        $req = (object) ['params' => (object) ['id' => '99']];
        $res = new Response();

        $this->expectOutputString(json_encode('La categoría con el id=99 no existe'));
        $controller->getCategory($req, $res);
        $this->assertTrue($res->hasFinished());
    }
}
