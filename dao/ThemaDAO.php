<?php
require_once __DIR__ . '/../config/Database.php';

class ThemaDAO {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    // 1. CREATE
    public function createThema($name) {
        $stmt = $this->pdo->prepare("INSERT INTO themen (name) VALUES (:name)");
        return $stmt->execute([':name' => $name]);
    }

    // 2. READ
    public function getAllThemen() {
        $stmt = $this->pdo->prepare("SELECT * FROM themen ORDER BY id DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // 3. UPDATE
    public function updateThema($id, $name) {
        $stmt = $this->pdo->prepare("UPDATE themen SET name = :name WHERE id = :id");
        return $stmt->execute([':name' => $name, ':id' => $id]);
    }

    // 4. DELETE
    public function deleteThema($id) {
        $stmt = $this->pdo->prepare("DELETE FROM themen WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}