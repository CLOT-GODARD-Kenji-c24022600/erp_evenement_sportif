<?php

/**
 * YES - Your Event Solution
 * @file SearchModel.php
 * @version 2.0  –  2026
 * AJOUTS : recherche dans todos, budget, contacts
 */

declare(strict_types=1);

namespace App\Models;

use Core\Database;
use PDO;
use PDOException;

class SearchModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

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
        } catch (PDOException) {
            return [];
        }
    }

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
        } catch (PDOException) {
            return [];
        }
    }

    public function searchProjets(string $term): array
    {
        try {
            $like = '%' . $term . '%';
            $stmt = $this->db->prepare(
                'SELECT * FROM projets WHERE nom LIKE :n OR description LIKE :d'
            );
            $stmt->execute(['n' => $like, 'd' => $like]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException) {
            return [];
        }
    }

    // ── NOUVEAUX ─────────────────────────────────────────────

    public function searchTodos(string $term): array
    {
        try {
            $like = '%' . $term . '%';
            $stmt = $this->db->prepare(
                "SELECT t.id, t.title, t.status, t.category, t.due_date,
                        e.nom AS event_nom, p.nom AS projet_nom
                 FROM todos t
                 LEFT JOIN evenements e ON e.id = t.event_id
                 LEFT JOIN projets    p ON p.id = t.projet_id
                 WHERE t.title LIKE :n OR t.description LIKE :d
                 ORDER BY t.due_date ASC
                 LIMIT 5"
            );
            $stmt->execute(['n' => $like, 'd' => $like]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException) {
            return [];
        }
    }

    public function searchBudget(string $term): array
    {
        try {
            $like = '%' . $term . '%';
            $stmt = $this->db->prepare(
                "SELECT b.id, b.libelle, b.type, b.previsionnel, b.categorie,
                        e.nom AS event_nom, e.id AS event_id,
                        p.nom AS projet_nom, p.id AS projet_id
                 FROM budget_lignes b
                 LEFT JOIN evenements e ON e.id = b.event_id
                 LEFT JOIN projets    p ON p.id = b.projet_id
                 WHERE b.libelle LIKE :n OR b.categorie LIKE :c OR b.fournisseur LIKE :f
                 LIMIT 5"
            );
            $stmt->execute(['n' => $like, 'c' => $like, 'f' => $like]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException) {
            return [];
        }
    }

    public function searchContacts(string $term): array
    {
        try {
            $like = '%' . $term . '%';
            $stmt = $this->db->prepare(
                'SELECT id, nom, telephone, mail, societe, poste, type
                 FROM contacts
                 WHERE nom LIKE :n OR societe LIKE :s OR mail LIKE :m OR telephone LIKE :t
                 LIMIT 5'
            );
            $stmt->execute(['n' => $like, 's' => $like, 'm' => $like, 't' => $like]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException) {
            return [];
        }
    }
}