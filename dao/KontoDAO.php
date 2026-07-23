<?php
require_once __DIR__ . '/../config/Database.php';
class KontoDAO {
    private PDO $pdo;
    public function __construct() { $this->pdo = Database::getInstance()->getConnection(); }
    public function getKontostand(int $kontoId = 1): float {
        $stmt=$this->pdo->prepare('SELECT k.kontostand + COALESCE(SUM(t.betrag),0) AS kontostand FROM konten k LEFT JOIN transaktionen t ON t.konto_id=k.id WHERE k.id=:id GROUP BY k.id,k.kontostand');
        $stmt->execute(['id'=>$kontoId]); $row=$stmt->fetch(); return $row?(float)$row['kontostand']:0.0;
    }
}