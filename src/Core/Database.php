<?php
namespace Core;

use PDO;
use PDOException;

class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            try {
                // Les variables sont lues depuis le .env via Docker
                $host = getenv('ALWAYSDATA_DB_HOST');
                $dbname = getenv('ALWAYSDATA_DB_NAME');
                $user = getenv('ALWAYSDATA_DB_USER');
                $pass = getenv('ALWAYSDATA_DB_PASSWORD');

                self::$instance = new PDO(
                    "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                    $user,
                    $pass,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
            } catch (PDOException $e) {
                die("❌ Erreur BDD : " . $e->getMessage());
            }
        }
        return self::$instance;
    }
}