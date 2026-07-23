<?php
require_once __DIR__ . '/../config/Database.php';
class ThemaDAO {
    private PDO $pdo;
    public function __construct() { $this->pdo = Database::getInstance()->getConnection(); }
    public function getAllThemen(): array { return $this->pdo->query('SELECT * FROM themen ORDER BY name')->fetchAll(); }
    public function createThema(string $name, string $typ): bool {
        $stmt=$this->pdo->prepare('INSERT INTO themen (name, typ) VALUES (:name,:typ)');
        return $stmt->execute(['name'=>trim($name),'typ'=>$typ]);
    }
}
