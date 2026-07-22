<?php
require_once __DIR__ . '/../config/Database.php';

class TagDAO {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    // 1. CREATE (Erstellen)
    public function createTag($name) {
        $stmt = $this->pdo->prepare("INSERT INTO tags (name) VALUES (:name)");
        return $stmt->execute([':name' => $name]);
    }

    // 2. READ (Lesen / Alle abfragen)
    public function getAllTags() {
        $stmt = $this->pdo->prepare("SELECT * FROM tags ORDER BY id DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // 3. UPDATE (Bearbeiten)
    public function updateTag($id, $name) {
        $stmt = $this->pdo->prepare("UPDATE tags SET name = :name WHERE id = :id");
        return $stmt->execute([':name' => $name, ':id' => $id]);
    }

    // 4. DELETE (Löschen)
    public function deleteTag($id) {
        $stmt = $this->pdo->prepare("DELETE FROM tags WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}