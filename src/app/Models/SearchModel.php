<?php

/**
 * YES - Your Event Solution
 *
 * ERP évènementiel
 *
 * @file SearchModel.php
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.0
 * @since 2026
 */

declare(strict_types=1);

namespace App\Models;

use Core\Database;
use PDO;
use PDOException;

/**
 * Modèle de recherche globale.
 *
 * Effectue des recherches dans le staff, les événements et les projets.
 */
class SearchModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Recherche dans les membres du staff.
     *
     * @param string $term Terme de recherche.
     * @return array[]
     */
    public function searchStaff(string $term): array
    {
        try {
            $like = '%' . $term . '%';
            $stmt = $this->db->prepare(
                "SELECT * FROM utilisateurs
                 WHERE (nom LIKE :n OR prenom LIKE :p OR poste LIKE :po)
                 AND statut = 'approuve'"
            );
            $stmt->execute(['n' => $like, 'p' => $like, 'po' => $like]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Recherche dans les événements.
     *
     * @param string $term Terme de recherche.
     * @return array[]
     */
    public function searchEvents(string $term): array
    {
        try {
            $like = '%' . $term . '%';
            $stmt = $this->db->prepare(
                'SELECT * FROM evenements
                 WHERE nom LIKE :n OR lieu LIKE :l OR description LIKE :d'
            );
            $stmt->execute(['n' => $like, 'l' => $like, 'd' => $like]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Recherche dans les projets.
     *
     * @param string $term Terme de recherche.
     * @return array[]
     */
    public function searchProjets(string $term): array
    {
        try {
            $like = '%' . $term . '%';
            $stmt = $this->db->prepare(
                'SELECT * FROM projets WHERE nom LIKE :n OR description LIKE :d'
            );
            $stmt->execute(['n' => $like, 'd' => $like]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}
