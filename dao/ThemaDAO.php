<?php
require_once __DIR__ . '/../config/Database.php';
class ThemaDAO {
    private PDO $pdo;
    public function __construct() { $this->pdo = Database::getInstance()->getConnection(); }
   // public function getAllThemen(): array { return $this->pdo->query('SELECT * FROM themen ORDER BY name')->fetchAll(); }
   // public function createThema(string $name, string $typ): bool {
   //     $stmt = $this->pdo->prepare('INSERT INTO themen (name, typ) VALUES (:name, :typ)');
   //     return $stmt->execute(['name' => trim($name), 'typ' => $typ]);
   // }

    // 1. CREATE
    public function createThema(string $name, string $typ): bool {
        $stmt = $this->pdo->prepare("INSERT INTO themen (name, typ) VALUES (:name, :typ)");
        return $stmt->execute([':name' => trim($name), ':typ' => $typ]);
    }

    // 2. READ
    public function getAllThemen() {
        $stmt = $this->pdo->prepare("SELECT * FROM themen ORDER BY id DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }


    public function updateThema(int $id, string $name, string $typ): bool {
        $stmt = $this->pdo->prepare('UPDATE themen SET name = :name, typ = :typ WHERE id = :id');
        return $stmt->execute(['id' => $id, 'name' => trim($name), 'typ' => $typ]);
    }
    public function deleteThema(int $id): bool {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM transaktionen WHERE thema_id = ?');
        $stmt->execute([$id]);
        if ((int)$stmt->fetchColumn() > 0) {
            throw new RuntimeException('Die Kategorie wird noch von Buchungen verwendet.');
        }
        return $this->pdo->prepare('DELETE FROM themen WHERE id = ?')->execute([$id]);
    }
}

