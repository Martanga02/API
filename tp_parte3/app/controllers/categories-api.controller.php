<?php
require_once './app/models/categories.model.php';

class CategoryApiController {
    private $model;

    public function __construct() {
        $this->model = new CategoriesModel();
        // no hay vista en la API REST
    }

    // (opcional) GET con filtrado
    public function getCategories($req, $res) {
        // Si el cliente envía un parámetro de filtro (?nombre=algo)
        if (isset($req->query->nombre)) {
            $categories = $this->model->getAllFilterByName($req->query->nombre);
        } else {
            // Si no envía filtro, devolvemos todas
            $categories = $this->model->getAll();
        }
    
        // Devolvemos las categorías con código 200 OK
        return $res->json($categories, 200);
    }
    
    // GET por ID (obligatorio)
    public function getCategory($req, $res) {
        $idCategoria = $req->params->id;

        $categoria = $this->model->get($idCategoria);
        
        if (!$categoria) {
            return $res->json("La categoría con el id=$idCategoria no existe", 404);
        }

        return $res->json($categoria, 200);
    }

    // POST (crear categoría)
    public function insertCategory($req, $res) {
        if (empty($req->body->nombre) || empty($req->body->descripcion)) {
            return $res->json('Faltan datos', 400);
        }

        $nombre = $req->body->nombre;
        $descripcion = $req->body->descripcion;

        $newId = $this->model->insert($nombre, $descripcion);

        if ($newId == false) {
            return $res->json('Error del servidor', 500);
        }

        $newCategoria = $this->model->get($newId);
        return $res->json($newCategoria, 201);
    }
}
