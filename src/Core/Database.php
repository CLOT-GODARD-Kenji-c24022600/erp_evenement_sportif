<?php
namespace Core;

use PDO;
use PDOException;
use RuntimeException;

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

                if (!$host || !$dbname || !$user) {
                    throw new RuntimeException('Variables BDD manquantes dans l\'environnement.');
                }

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
                throw new RuntimeException('Erreur BDD : ' . $e->getMessage(), 0, $e);
            }
        }
        return self::$instance;
    }
}