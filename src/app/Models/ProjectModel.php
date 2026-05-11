<?php

declare(strict_types=1);

namespace App\Models;

use Core\Database;
use PDO;
use PDOException;

class ProjectModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // ── Projets CRUD ─────────────────────────────────────────────

    public function getAll(): array
    {
        try {
            return $this->db->query(
                "SELECT p.*,
                        COALESCE(SUM(CASE WHEN pf.type = 'recette' THEN pf.montant ELSE 0 END), 0) AS total_recettes,
                        COALESCE(SUM(CASE WHEN pf.type = 'depense' THEN pf.montant ELSE 0 END), 0) AS total_depenses,
                        COUNT(DISTINCT e.id) AS nb_evenements
                 FROM projets p
                 LEFT JOIN projet_finance pf ON pf.projet_id = p.id
                 LEFT JOIN evenements e      ON e.projet_id  = p.id
                 GROUP BY p.id
                 ORDER BY p.date_creation DESC"
            )->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException) {
            return [];
        }
    }

    public function findById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT p.*,
                        COALESCE(SUM(CASE WHEN pf.type = 'recette' THEN pf.montant ELSE 0 END), 0) AS total_recettes,
                        COALESCE(SUM(CASE WHEN pf.type = 'depense' THEN pf.montant ELSE 0 END), 0) AS total_depenses,
                        COUNT(DISTINCT e.id) AS nb_evenements
                 FROM projets p
                 LEFT JOIN projet_finance pf ON pf.projet_id = p.id
                 LEFT JOIN evenements e      ON e.projet_id  = p.id
                 WHERE p.id = :id
                 GROUP BY p.id
                 LIMIT 1"
            );
            $stmt->execute(['id' => $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row !== false ? $row : null;
        } catch (PDOException) {
            return null;
        }
    }

    public function create(array $data): bool
    {
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO projets (nom, description, statut, budget, date_debut, date_fin, date_creation)
                 VALUES (:nom, :description, :statut, :budget, :date_debut, :date_fin, NOW())'
            );
            return $stmt->execute([
                'nom'         => $data['nom'],
                'description' => $data['description'] ?? null,
                'statut'      => $data['statut']      ?? 'en_cours',
                'budget'      => !empty($data['budget']) ? (float) $data['budget'] : null,
                'date_debut'  => !empty($data['date_debut']) ? $data['date_debut'] : null,
                'date_fin'    => !empty($data['date_fin'])   ? $data['date_fin']   : null,
            ]);
        } catch (PDOException) {
            return false;
        }
    }

    public function update(int $id, array $data): bool
    {
        try {
            $stmt = $this->db->prepare(
                'UPDATE projets
                 SET nom = :nom, description = :description, statut = :statut,
                     budget = :budget, date_debut = :date_debut, date_fin = :date_fin
                 WHERE id = :id'
            );
            return $stmt->execute([
                'id'          => $id,
                'nom'         => $data['nom'],
                'description' => $data['description'] ?? null,
                'statut'      => $data['statut']      ?? 'en_cours',
                'budget'      => !empty($data['budget']) ? (float) $data['budget'] : null,
                'date_debut'  => !empty($data['date_debut']) ? $data['date_debut'] : null,
                'date_fin'    => !empty($data['date_fin'])   ? $data['date_fin']   : null,
            ]);
        } catch (PDOException) {
            return false;
        }
    }

    public function delete(int $id): bool
    {
        try {
            $this->db->prepare('DELETE FROM projet_finance WHERE projet_id = :id')->execute(['id' => $id]);
            return $this->db->prepare('DELETE FROM projets WHERE id = :id')->execute(['id' => $id]);
        } catch (PDOException) {
            return false;
        }
    }

    // ── Finance ──────────────────────────────────────────────────

    public function getFinance(int $projetId): array
    {
        try {
            $stmt = $this->db->prepare(
                'SELECT * FROM projet_finance WHERE projet_id = :id ORDER BY date_operation DESC, id DESC'
            );
            $stmt->execute(['id' => $projetId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException) {
            return [];
        }
    }

    public function addFinanceLine(array $data): bool
    {
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO projet_finance (projet_id, type, categorie, libelle, montant, date_operation, note)
                 VALUES (:projet_id, :type, :categorie, :libelle, :montant, :date_operation, :note)'
            );
            return $stmt->execute([
                'projet_id'      => (int) $data['projet_id'],
                'type'           => $data['type'],
                'categorie'      => $data['categorie']      ?? null,
                'libelle'        => $data['libelle'],
                'montant'        => (float) $data['montant'],
                'date_operation' => $data['date_operation'] ?? date('Y-m-d'),
                'note'           => $data['note']            ?? null,
            ]);
        } catch (PDOException) {
            return false;
        }
    }

    public function deleteFinanceLine(int $id, int $projetId): bool
    {
        try {
            $stmt = $this->db->prepare(
                'DELETE FROM projet_finance WHERE id = :id AND projet_id = :projet_id'
            );
            return $stmt->execute(['id' => $id, 'projet_id' => $projetId]);
        } catch (PDOException) {
            return false;
        }
    }

    // ── Événements liés ──────────────────────────────────────────

    public function getEvents(int $projetId): array
    {
        try {
            $stmt = $this->db->prepare(
                'SELECT * FROM evenements WHERE projet_id = :id ORDER BY date_debut ASC'
            );
            $stmt->execute(['id' => $projetId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException) {
            return [];
        }
    }

    public function getAllSimple(): array
    {
        try {
            return $this->db->query(
                'SELECT id, nom FROM projets ORDER BY nom ASC'
            )->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException) {
            return [];
        }
    }
}