<?php
require_once __DIR__ . '/../config/Database.php';
class TagDAO {
    private PDO $pdo;
    public function __construct() { $this->pdo = Database::getInstance()->getConnection(); }
    public function getAllTags(): array { return $this->pdo->query('SELECT * FROM tags ORDER BY name')->fetchAll(); }
    public function createTag(string $name): bool {
        $stmt=$this->pdo->prepare('INSERT INTO tags (name) VALUES (:name)');
        return $stmt->execute(['name'=>trim($name)]);
    }
}
