<?php

/**
 * YES - Your Event Solution
 *
 * ERP évènementiel
 *
 * @file Database.php
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.0
 * @since 2026
 */

declare(strict_types=1);

namespace Core;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Singleton de connexion PDO à la base de données.
 *
 * Lit les variables d'environnement injectées par Docker/AlwaysData
 * et retourne une connexion unique réutilisée tout au long de la requête.
 */
class Database
{
    /** @var PDO|null Instance unique PDO */
    private static ?PDO $instance = null;

    /** Empêche l'instanciation directe */
    private function __construct() {}

    /**
     * Retourne la connexion PDO (la crée si nécessaire).
     *
     * @throws RuntimeException Si les variables d'environnement sont manquantes ou si la connexion échoue.
     * @return PDO
     */
    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $host   = (string) getenv('ALWAYSDATA_DB_HOST');
            $dbname = (string) getenv('ALWAYSDATA_DB_NAME');
            $user   = (string) getenv('ALWAYSDATA_DB_USER');
            $pass   = (string) getenv('ALWAYSDATA_DB_PASSWORD');

            if (empty($host) || empty($dbname) || empty($user)) {
                throw new RuntimeException('Variables BDD manquantes dans l\'environnement.');
            }

            try {
                self::$instance = new PDO(
                    "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
                    $user,
                    $pass,
                    [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES   => false,
                    ]
                );
            } catch (PDOException $e) {
                throw new RuntimeException('Erreur de connexion BDD : ' . $e->getMessage(), 0, $e);
            }
        }

        return self::$instance;
    }
}
