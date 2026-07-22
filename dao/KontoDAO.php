<?php
require_once __DIR__ . '/../config/Database.php';

class KontoDAO {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    // 1. CREATE
    public function createKonto($name) {
        $stmt = $this->pdo->prepare("INSERT INTO konten (name) VALUES (:name)");
        return $stmt->execute([':name' => $name]);
    }

    // 2. READ
    public function getAllKonten() {
        $stmt = $this->pdo->prepare("SELECT * FROM konten ORDER BY id DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // 3. UPDATE
    public function updateKonto($id, $name) {
        $stmt = $this->pdo->prepare("UPDATE konten SET name = :name WHERE id = :id");
        return $stmt->execute([':name' => $name, ':id' => $id]);
    }

    // 4. DELETE
    public function deleteKonto($id) {
        $stmt = $this->pdo->prepare("DELETE FROM konten WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}