<?php
require_once 'config.php';

class PeliculaModel {
    private $db;

    public function __construct() {
        $this->db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8", DB_USER, DB_PASS);
    }

    public function getAll($sort = null, $order = null) {
        $sql = "SELECT * FROM pelicula";

        $allowedSorts = ['nombre', 'estudio', 'id_pelicula', 'id_genero'];
        $allowedOrders = ['ASC', 'DESC', 'asc', 'desc'];

        if ($sort != null && in_array($sort, $allowedSorts)) {
            $sql .= " ORDER BY $sort ";
            if ($order != null && in_array($order, $allowedOrders)) {
                $sql .= $order;
            }
        }

        $query = $this->db->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_OBJ);
    }

    public function get($id) {
        $query = $this->db->prepare("SELECT * FROM pelicula WHERE id_pelicula = ?");
        $query->execute([$id]);
        return $query->fetch(PDO::FETCH_OBJ);
    }

    public function update($id, $nombre, $estudio, $id_genero) {
        $sql = "UPDATE pelicula SET nombre = ?, estudio = ?, id_genero = ? WHERE id_pelicula = ?";
        $query = $this->db->prepare($sql);
        $query->execute([$nombre, $estudio, $id_genero, $id]);
    }
}