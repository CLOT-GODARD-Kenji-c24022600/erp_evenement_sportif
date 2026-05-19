<?php

/**
 * YES – Your Event Solution
 * @file PlanningModel.php
 * @version 1.0  –  2026
 */

declare(strict_types=1);

namespace App\Models;

use Core\Database;
use PDO;
use PDOException;

class PlanningModel
{
    private PDO $db;

    private const STATUTS_ALLOWED = [
        'wip', 'valide', 'maj', 'devis', 'visuels', 'bat', 'prod', 'en_cours', 'annule'
    ];

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getByEvent(int $eventId): array
    {
        try {
            $stmt = $this->db->prepare(
                'SELECT * FROM planning_lignes WHERE event_id = :id ORDER BY ordre ASC, id ASC'
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
                'SELECT * FROM planning_lignes WHERE projet_id = :id ORDER BY ordre ASC, id ASC'
            );
            $stmt->execute(['id' => $projetId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException) {
            return [];
        }
    }

    public function create(array $d): bool
    {
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO planning_lignes (event_id, projet_id, tache, statut, date_debut, date_fin, note, ordre)
                 VALUES (:event_id, :projet_id, :tache, :statut, :date_debut, :date_fin, :note, :ordre)'
            );
            return $stmt->execute([
                'event_id'   => $d['event_id']  ?? null,
                'projet_id'  => $d['projet_id'] ?? null,
                'tache'      => $d['tache']      ?? '',
                'statut'     => in_array($d['statut'] ?? 'wip', self::STATUTS_ALLOWED, true) ? $d['statut'] : 'wip',
                'date_debut' => !empty($d['date_debut']) ? $d['date_debut'] : null,
                'date_fin'   => !empty($d['date_fin'])   ? $d['date_fin']   : null,
                'note'       => $d['note']       ?? null,
                'ordre'      => (int) ($d['ordre'] ?? 0),
            ]);
        } catch (PDOException) {
            return false;
        }
    }

    public function update(int $id, array $d): bool
    {
        try {
            $stmt = $this->db->prepare(
                'UPDATE planning_lignes SET
                    tache=:tache, statut=:statut, date_debut=:date_debut,
                    date_fin=:date_fin, note=:note, ordre=:ordre
                 WHERE id=:id'
            );
            return $stmt->execute([
                'tache'      => $d['tache']  ?? '',
                'statut'     => in_array($d['statut'] ?? 'wip', self::STATUTS_ALLOWED, true) ? $d['statut'] : 'wip',
                'date_debut' => !empty($d['date_debut']) ? $d['date_debut'] : null,
                'date_fin'   => !empty($d['date_fin'])   ? $d['date_fin']   : null,
                'note'       => $d['note']   ?? null,
                'ordre'      => (int) ($d['ordre'] ?? 0),
                'id'         => $id,
            ]);
        } catch (PDOException) {
            return false;
        }
    }

    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare('DELETE FROM planning_lignes WHERE id = :id');
            return $stmt->execute(['id' => $id]);
        } catch (PDOException) {
            return false;
        }
    }

    public function updateStatut(int $id, string $statut): bool
    {
        if (!in_array($statut, self::STATUTS_ALLOWED, true)) return false;
        try {
            $stmt = $this->db->prepare('UPDATE planning_lignes SET statut=:statut WHERE id=:id');
            return $stmt->execute(['statut' => $statut, 'id' => $id]);
        } catch (PDOException) {
            return false;
        }
    }
}