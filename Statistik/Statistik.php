<?php
require_once __DIR__ . '/../config/Database.php';

class Statistik {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    // Berechnet die Gesamtsumme der Einnahmen
    public function getGesamteinnahmen() {
        $stmt = $this->pdo->query("SELECT SUM(betrag) AS total FROM transaktionen WHERE betrag > 0");
        $row = $stmt->fetch();
        return $row['total'] ? (float)$row['total'] : 0.00;
    }

    // Berechnet die Gesamtsumme der Ausgaben
    public function getGesamtausgaben() {
        $stmt = $this->pdo->query("SELECT SUM(betrag) AS total FROM transaktionen WHERE betrag < 0");
        $row = $stmt->fetch();
        return $row['total'] ? (float)$row['total'] : 0.00;
    }
}