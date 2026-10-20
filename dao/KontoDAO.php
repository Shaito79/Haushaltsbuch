<?php
require_once __DIR__ . '/../config/Database.php';
class KontoDAO {
    private PDO $pdo;
    public function __construct() { $this->pdo = Database::getInstance()->getConnection(); }
    public function getKontostand(int $kontoId = 1): float {
        $stmt=$this->pdo->prepare('SELECT k.kontostand + COALESCE(SUM(t.betrag),0) AS kontostand FROM konten k LEFT JOIN transaktionen t ON t.konto_id=k.id WHERE k.id=:id GROUP BY k.id,k.kontostand');
        $stmt->execute(['id'=>$kontoId]); $row=$stmt->fetch(); return $row?(float)$row['kontostand']:0.0;
    }

    public function getAllKonten(): array {
        return $this->pdo->query('SELECT * FROM konten ORDER BY id')->fetchAll();
    }

    public function createKonto(string $name): bool {
        $stmt = $this->pdo->prepare('INSERT INTO konten (name) VALUES (:name)');
        return $stmt->execute(['name' => trim($name)]);
    }

    public function updateKonto(int $id, string $name): bool {
        $stmt = $this->pdo->prepare('UPDATE konten SET name = :name WHERE id = :id');
        return $stmt->execute(['id' => $id, 'name' => trim($name)]);
    }

    // Transaktionen hängen per ON DELETE CASCADE am Konto, daher vorher prüfen,
    // damit beim Löschen keine Buchungen unbemerkt mitgelöscht werden.
    public function deleteKonto(int $id): bool {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM transaktionen WHERE konto_id = ?');
        $stmt->execute([$id]);
        if ((int)$stmt->fetchColumn() > 0) {
            throw new RuntimeException('Dieses Konto kann nicht gelöscht werden, da noch Transaktionen darauf gebucht sind.');
        }
        return $this->pdo->prepare('DELETE FROM konten WHERE id = ?')->execute([$id]);
    }
}
