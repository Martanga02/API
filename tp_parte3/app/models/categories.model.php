<?php
require_once 'config.php'; 

class CategoriesModel {
    private $db;

    public function __construct(?PDO $db = null) {
        // Conexión a la base (usa la DB compartida), o la conexión inyectada (p. ej. en tests)
        $this->db = $db ?? new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8', DB_USER, DB_PASS);
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
    // Obtiene todas las categorías filtradas por nombre (LIKE)
    public function getAllFilterByName($name) {
        $query = $this->db->prepare('SELECT * FROM genero WHERE nombre LIKE ?');
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
        $query = $this->db->prepare("INSERT INTO genero (nombre, descripcion) VALUES (?, ?)");
        $query->execute([$nombre, $descripcion]);
        return $this->db->lastInsertId();
    }
}
