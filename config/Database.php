<?php

declare(strict_types=1);

class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;

    private function __construct()
    {
        $configPath = __DIR__ . '/database.ini';

        if (!is_file($configPath)) {
            throw new RuntimeException('Datenbank-Konfiguration nicht gefunden: ' . $configPath);
        }

        $config = parse_ini_file($configPath, false, INI_SCANNER_RAW);

        if ($config === false) {
            throw new RuntimeException('Die Datei config/database.ini konnte nicht gelesen werden.');
        }

        foreach (['host', 'port', 'database', 'username', 'password', 'charset'] as $key) {
            if (!array_key_exists($key, $config)) {
                throw new RuntimeException('Fehlender Eintrag in database.ini: ' . $key);
            }
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            (string) $config['host'],
            (int) $config['port'],
            (string) $config['database'],
            (string) $config['charset']
        );

        try {
            $this->pdo = new PDO(
                $dsn,
                (string) $config['username'],
                (string) $config['password'],
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            throw new RuntimeException(
                'Datenbankverbindung fehlgeschlagen: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }

        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }
}
