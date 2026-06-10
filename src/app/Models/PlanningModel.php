<?php

/**
 * YES – Your Event Solution
 * @file PlanningModel.php
 * @version 1.1  –  2026
 * CORRECTION : table = planning_lignes (nom réel en BDD)
 */

declare(strict_types=1);

namespace App\Models;

use Core\Database;
use PDO;
use PDOException;

class PlanningModel
{
    private PDO $db;

    private const TABLE = 'planning_lignes';

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
                'SELECT * FROM ' . self::TABLE . ' WHERE event_id = :id ORDER BY ordre ASC, id ASC'
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
                'SELECT * FROM ' . self::TABLE . ' WHERE projet_id = :id ORDER BY ordre ASC, id ASC'
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
                'INSERT INTO ' . self::TABLE . '
                    (event_id, projet_id, tache, statut, date_debut, date_fin, note, ordre)
                 VALUES
                    (:event_id, :projet_id, :tache, :statut, :date_debut, :date_fin, :note, :ordre)'
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
                'UPDATE ' . self::TABLE . ' SET
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
            $stmt = $this->db->prepare('DELETE FROM ' . self::TABLE . ' WHERE id = :id');
            return $stmt->execute(['id' => $id]);
        } catch (PDOException) {
            return false;
        }
    }

    public function updateStatut(int $id, string $statut): bool
    {
        if (!in_array($statut, self::STATUTS_ALLOWED, true)) return false;
        try {
            $stmt = $this->db->prepare('UPDATE ' . self::TABLE . ' SET statut=:statut WHERE id=:id');
            return $stmt->execute(['statut' => $statut, 'id' => $id]);
        } catch (PDOException) {
            return false;
        }
    }

    /**
     * Synchronise les phases de préproduction d'un événement vers le planning.
     * Si une tâche liée à la phase existe déjà (via source_phase), on la met à jour.
     * Sinon on la crée. Si la phase n'a plus de dates, on supprime la tâche correspondante.
     */
    public function syncPhasesToPlanning(int $eventId, array $phases): void
    {
        // Vérifie si la colonne source_phase existe, sinon on tente de l'ajouter
        try {
            $this->db->query('SELECT source_phase FROM ' . self::TABLE . ' LIMIT 1');
        } catch (PDOException) {
            // Colonne absente : on l'ajoute
            try {
                $this->db->exec('ALTER TABLE ' . self::TABLE . ' ADD COLUMN source_phase VARCHAR(50) DEFAULT NULL');
            } catch (PDOException) {
                return; // Si l'ALTER échoue on abandonne silencieusement
            }
        }

        foreach ($phases as $phaseKey => $phase) {
            $debut = $phase['debut'] ?? null;
            $fin   = $phase['fin']   ?? null;
            $label = $phase['label'];

            // Chercher une tâche existante liée à cette phase pour cet événement
            $stmt = $this->db->prepare(
                'SELECT id FROM ' . self::TABLE .
                ' WHERE event_id = :eid AND source_phase = :phase LIMIT 1'
            );
            $stmt->execute(['eid' => $eventId, 'phase' => $phaseKey]);
            $existing = $stmt->fetchColumn();

            if (!$debut && !$fin) {
                // Phase supprimée → retirer la tâche du planning si elle existe
                if ($existing) {
                    $this->db->prepare('DELETE FROM ' . self::TABLE . ' WHERE id = :id')
                             ->execute(['id' => $existing]);
                }
                continue;
            }

            if ($existing) {
                // Mise à jour de la tâche existante (dates seulement, on ne touche pas au statut)
                $upd = $this->db->prepare(
                    'UPDATE ' . self::TABLE .
                    ' SET date_debut = :deb, date_fin = :fin WHERE id = :id'
                );
                $upd->execute(['deb' => $debut, 'fin' => $fin, 'id' => $existing]);
            } else {
                // Création d'une nouvelle tâche de planning
                $ins = $this->db->prepare(
                    'INSERT INTO ' . self::TABLE .
                    ' (event_id, tache, statut, date_debut, date_fin, note, ordre, source_phase)
                     VALUES (:eid, :tache, :statut, :deb, :fin, :note, :ordre, :phase)'
                );
                $ins->execute([
                    'eid'    => $eventId,
                    'tache'  => $label,
                    'statut' => 'wip',
                    'deb'    => $debut,
                    'fin'    => $fin,
                    'note'   => 'Synchronisé depuis la préproduction',
                    'ordre'  => 0,
                    'phase'  => $phaseKey,
                ]);
            }
        }
    }
}