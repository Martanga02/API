<?php
require_once 'config.php';

class UserModel {
    private $db;

    public function __construct(?PDO $db = null) {
        // Usa la conexión inyectada (tests) o la de la base real
        $this->db = $db ?? db_connect();
    }

    public function get($id) {
        $query = $this->db->prepare('SELECT * FROM usuarios WHERE id = ?');
        $query->execute([$id]);
        return $query->fetch(PDO::FETCH_OBJ);
    }

    public function getByUser($user) {
        $query = $this->db->prepare('SELECT * FROM usuarios WHERE usuario = ?');
        $query->execute([$user]);
        return $query->fetch(PDO::FETCH_OBJ);
    }

    public function getAll() {
        $query = $this->db->prepare('SELECT * FROM usuarios');
        $query->execute();
        return $query->fetchAll(PDO::FETCH_OBJ);
    }

    public function insert($user, $password) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $query = $this->db->prepare("INSERT INTO usuarios(usuario, contrasena) VALUES(?, ?) RETURNING id");
        $query->execute([$user, $hash]);
        return $query->fetchColumn();
    }
}
