<?php
require_once __DIR__ . '/../config/Database.php';

class TagDAO {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    // Holt alle Schlagworte / Tags
    public function getAllTags() {
        $stmt = $this->pdo->query("SELECT * FROM tags ORDER BY name ASC");
        return $stmt->fetchAll();
    }
}