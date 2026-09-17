<?php
require_once 'app/models/pelicula.model.php';
require_once 'app/views/api.view.php';

class PeliculaApiController {
    private $model;
    private $view;

    public function __construct() {
        $this->model = new PeliculaModel();
        $this->view = new ApiView();
    }

    public function getAll($req, $res) {
        $sort = null;
        $order = null;

        if (isset($req->query->sort)) {
            $sort = $req->query->sort;
        }
        if (isset($req->query->order)) {
            $order = $req->query->order;
        }

        $peliculas = $this->model->getAll($sort, $order);
        
        // return $this->view->response($peliculas, 200);
        return $res->json($peliculas, 200);
    }

    public function update($req, $res) {
        $id = $req->params->id;

        $pelicula = $this->model->get($id);
        if (!$pelicula) {
            return $this->view->response("La película con el id=$id no existe", 404);
        }

        if (empty($req->body->nombre) || empty($req->body->estudio) || empty($req->body->id_genero)) {
            return $this->view->response("Faltan completar datos obligatorios", 400);
        }

        $nombre = $req->body->nombre;
        $estudio = $req->body->estudio;
        $id_genero = $req->body->id_genero;

        $this->model->update($id, $nombre, $estudio, $id_genero);

        $peliculaActualizada = $this->model->get($id);
        return $this->view->response($peliculaActualizada, 200);
    }
}