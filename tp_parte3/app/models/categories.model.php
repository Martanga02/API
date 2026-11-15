<?php
require_once 'config.php'; 

class CategoriesModel {
    private $db;

    public function __construct(?PDO $db = null) {
        // Conexión a la base (usa la DB compartida), o la conexión inyectada (p. ej. en tests)
        $this->db = $db ?? db_connect();
    }

    // Obtiene una categoría por ID
    public function get($id) {
        $query = $this->db->prepare('SELECT * FROM genero WHERE id = ?');
        $query->execute([$id]);
        return $query->fetch(PDO::FETCH_OBJ);
    }

    // Obtiene todas las categorías
    public function getAll() {
        $query = $this->db->prepare('SELECT * FROM genero');
        $query->execute();
        return $query->fetchAll(PDO::FETCH_OBJ);
    }
    // Obtiene todas las categorías filtradas por nombre (ILIKE: no distingue mayúsculas, como el LIKE de MySQL)
    public function getAllFilterByName($name) {
        $query = $this->db->prepare('SELECT * FROM genero WHERE nombre ILIKE ?');
        $query->execute(["%$name%"]);
        $categories = $query->fetchAll(PDO::FETCH_OBJ);
        return $categories;
    }
    
    // Opcional: filtrado por tipo o estado
    public function getAllFilterByTipo($tipo) {
        $query = $this->db->prepare('SELECT * FROM genero WHERE tipo = ?');
        $query->execute([$tipo]);
        return $query->fetchAll(PDO::FETCH_OBJ);
    }

    // Inserta una nueva categoría
    public function insert($nombre, $descripcion) {
        // PostgreSQL devuelve el id nuevo con RETURNING (lastInsertId necesita el nombre de la secuencia)
        $query = $this->db->prepare("INSERT INTO genero (nombre, descripcion) VALUES (?, ?) RETURNING id");
        $query->execute([$nombre, $descripcion]);
        return $query->fetchColumn();
    }
}
