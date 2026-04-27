<?php
namespace Core;

use PDO;
use PDOException;

class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            try {
                // On récupère les infos du .env (chargées par Docker)
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
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]
                );
            } catch (PDOException $e) {
                die("Erreur de connexion à Alwaysdata : " . $e->getMessage());
            }
        }
        return self::$instance;
    }
}