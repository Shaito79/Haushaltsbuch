<?php
require_once __DIR__ . '/../config/Database.php';

class ThemaDAO {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    // Holt alle Kategorien/Themen für das Dropdown-Menü
    public function getAllThemen() {
        $stmt = $this->pdo->query("SELECT * FROM themen ORDER BY name ASC");
        return $stmt->fetchAll();
    }
}