<?php
require_once __DIR__ . '/../config/Database.php';

class KontoDAO {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    // Liest den aktuellen Kontostand ab
    public function getKontostand() {
        $stmt = $this->pdo->query("SELECT kontostand FROM konten LIMIT 1");
        $row = $stmt->fetch();
        return $row ? (float)$row['kontostand'] : 0.00;
    }
}