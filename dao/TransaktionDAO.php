<?php
require_once __DIR__ . '/../config/Database.php';

class TransaktionDAO {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    // Holt alle Transaktionen
    public function getAllTransaktionen() {
        $stmt = $this->pdo->query("SELECT * FROM transaktionen ORDER BY datum DESC");
        return $stmt->fetchAll();
    }

    // Erstellt eine neue Transaktion
    public function createTransaktion($konto_id, $thema_id, $betrag, $beschreibung, $datum) {
        $sql = "INSERT INTO transaktionen (konto_id, thema_id, betrag, beschreibung, datum) 
                VALUES (:konto_id, :thema_id, :betrag, :beschreibung, :datum)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'konto_id' => $konto_id,
            'thema_id' => $thema_id,
            'betrag' => $betrag,
            'beschreibung' => $beschreibung,
            'datum' => $datum
        ]);
    }

    // Löscht eine Transaktion
    public function deleteTransaktion($id) {
        $stmt = $this->pdo->prepare("DELETE FROM transaktionen WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}