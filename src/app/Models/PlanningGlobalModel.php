<?php

/**
 * YES – Your Event Solution
 * @file PlanningGlobalModel.php
 * @version 1.0  –  2026
 */

declare(strict_types=1);

namespace App\Models;

use Core\Database;
use PDO;
use PDOException;

class PlanningGlobalModel
{
    private PDO $db;

    private const STATUTS_ALLOWED = ['wip', 'en_cours', 'valide', 'annule'];

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAll(): array
    {
        try {
            $stmt = $this->db->query(
                'SELECT pg.*, e.nom AS event_nom, p.nom AS projet_nom
                 FROM planning_global pg
                 LEFT JOIN evenements e ON e.id = pg.event_id
                 LEFT JOIN projets p    ON p.id = pg.projet_id
                 ORDER BY pg.date_debut ASC'
            );
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException) {
            return [];
        }
    }

    public function getByEvent(int $eventId): array
    {
        try {
            $stmt = $this->db->prepare(
                'SELECT * FROM planning_global WHERE event_id = :id ORDER BY date_debut ASC'
            );
            $stmt->execute(['id' => $eventId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException) {
            return [];
        }
    }

    public function getByProjet(int $projetId): array
    {
        try {
            $stmt = $this->db->prepare(
                'SELECT * FROM planning_global WHERE projet_id = :id ORDER BY date_debut ASC'
            );
            $stmt->execute(['id' => $projetId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException) {
            return [];
        }
    }

    public function findById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare('SELECT * FROM planning_global WHERE id = :id LIMIT 1');
            $stmt->execute(['id' => $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row !== false ? $row : null;
        } catch (PDOException) {
            return null;
        }
    }

    public function create(array $d): bool
    {
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO planning_global
                    (titre, description, couleur, date_debut, date_fin, statut, event_id, projet_id)
                 VALUES
                    (:titre, :description, :couleur, :date_debut, :date_fin, :statut, :event_id, :projet_id)'
            );
            return $stmt->execute([
                'titre'       => $d['titre']       ?? '',
                'description' => $d['description'] ?? null,
                'couleur'     => $d['couleur']      ?? '#0d6efd',
                'date_debut'  => $d['date_debut'],
                'date_fin'    => $d['date_fin'],
                'statut'      => in_array($d['statut'] ?? 'wip', self::STATUTS_ALLOWED, true) ? $d['statut'] : 'wip',
                'event_id'    => $d['event_id']    ? (int)$d['event_id']   : null,
                'projet_id'   => $d['projet_id']   ? (int)$d['projet_id']  : null,
            ]);
        } catch (PDOException) {
            return false;
        }
    }

    public function update(int $id, array $d): bool
    {
        try {
            $stmt = $this->db->prepare(
                'UPDATE planning_global SET
                    titre=:titre, description=:description, couleur=:couleur,
                    date_debut=:date_debut, date_fin=:date_fin,
                    statut=:statut, event_id=:event_id, projet_id=:projet_id
                 WHERE id=:id'
            );
            return $stmt->execute([
                'titre'       => $d['titre']       ?? '',
                'description' => $d['description'] ?? null,
                'couleur'     => $d['couleur']      ?? '#0d6efd',
                'date_debut'  => $d['date_debut'],
                'date_fin'    => $d['date_fin'],
                'statut'      => in_array($d['statut'] ?? 'wip', self::STATUTS_ALLOWED, true) ? $d['statut'] : 'wip',
                'event_id'    => $d['event_id']    ? (int)$d['event_id']  : null,
                'projet_id'   => $d['projet_id']   ? (int)$d['projet_id'] : null,
                'id'          => $id,
            ]);
        } catch (PDOException) {
            return false;
        }
    }

    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare('DELETE FROM planning_global WHERE id = :id');
            return $stmt->execute(['id' => $id]);
        } catch (PDOException) {
            return false;
        }
    }
}