<?php
require_once __DIR__ . '/../config/Database.php';
class TagDAO {
    private PDO $pdo;
    public function __construct() { $this->pdo = Database::getInstance()->getConnection(); }
    public function getAllTags(): array { return $this->pdo->query('SELECT * FROM tags ORDER BY name')->fetchAll(); }
    public function createTag(string $name): bool {
        $stmt = $this->pdo->prepare('INSERT INTO tags (name) VALUES (:name)');
        return $stmt->execute(['name' => trim($name)]);
    }
    public function updateTag(int $id, string $name): bool {
        $stmt = $this->pdo->prepare('UPDATE tags SET name = :name WHERE id = :id');
        return $stmt->execute(['id' => $id, 'name' => trim($name)]);
    }
    public function deleteTag(int $id): bool {
        $this->pdo->beginTransaction();
        try {
            $this->pdo->prepare('DELETE FROM transaktionen_tags WHERE tag_id = ?')->execute([$id]);
            $result = $this->pdo->prepare('DELETE FROM tags WHERE id = ?')->execute([$id]);
            $this->pdo->commit();
            return $result;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
