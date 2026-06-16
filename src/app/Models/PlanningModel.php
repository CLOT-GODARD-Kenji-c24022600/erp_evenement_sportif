<?php

/**
 * YES – Your Event Solution
 * @file PlanningModel.php
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 2.1
 * @since 2026
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

    // AJOUT : Pour afficher tout le planning sur le Dashboard
    public function getAll(): array
    {
        try {
            $stmt = $this->db->query(
                'SELECT p.*, e.nom AS event_nom, pr.nom AS projet_nom, c.nom AS contact_nom 
                 FROM ' . self::TABLE . ' p
                 LEFT JOIN evenements e ON p.event_id = e.id
                 LEFT JOIN projets pr ON p.projet_id = pr.id
                 LEFT JOIN contacts c ON p.contact_id = c.id
                 ORDER BY p.date_debut ASC, p.ordre ASC'
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
                'SELECT p.*, c.nom AS contact_nom 
                 FROM ' . self::TABLE . ' p
                 LEFT JOIN contacts c ON p.contact_id = c.id
                 WHERE p.event_id = :id 
                 ORDER BY p.ordre ASC, p.id ASC'
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
                'SELECT p.*, c.nom AS contact_nom 
                 FROM ' . self::TABLE . ' p
                 LEFT JOIN contacts c ON p.contact_id = c.id
                 WHERE p.projet_id = :id 
                 ORDER BY p.ordre ASC, p.id ASC'
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
                    (event_id, projet_id, tache, statut, date_debut, date_fin, note, ordre, contact_id)
                 VALUES
                    (:event_id, :projet_id, :tache, :statut, :date_debut, :date_fin, :note, :ordre, :contact_id)'
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
                'contact_id' => $d['contact_id'] ?? null,
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
                    date_fin=:date_fin, note=:note, ordre=:ordre, contact_id=:contact_id
                 WHERE id=:id'
            );
            return $stmt->execute([
                'tache'      => $d['tache']  ?? '',
                'statut'     => in_array($d['statut'] ?? 'wip', self::STATUTS_ALLOWED, true) ? $d['statut'] : 'wip',
                'date_debut' => !empty($d['date_debut']) ? $d['date_debut'] : null,
                'date_fin'   => !empty($d['date_fin'])   ? $d['date_fin']   : null,
                'note'       => $d['note']   ?? null,
                'ordre'      => (int) ($d['ordre'] ?? 0),
                'contact_id' => $d['contact_id'] ?? null,
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

    public function syncPhasesToPlanning(int $eventId, array $phases): void
    {
        try {
            $this->db->query('SELECT source_phase FROM ' . self::TABLE . ' LIMIT 1');
        } catch (PDOException) {
            try {
                $this->db->exec('ALTER TABLE ' . self::TABLE . ' ADD COLUMN source_phase VARCHAR(50) DEFAULT NULL');
            } catch (PDOException) {
                return; 
            }
        }

        foreach ($phases as $phaseKey => $phase) {
            $debut = $phase['debut'] ?? null;
            $fin   = $phase['fin']   ?? null;
            $label = $phase['label'];

            $stmt = $this->db->prepare(
                'SELECT id FROM ' . self::TABLE .
                ' WHERE event_id = :eid AND source_phase = :phase LIMIT 1'
            );
            $stmt->execute(['eid' => $eventId, 'phase' => $phaseKey]);
            $existing = $stmt->fetchColumn();

            if (!$debut && !$fin) {
                if ($existing) {
                    $this->db->prepare('DELETE FROM ' . self::TABLE . ' WHERE id = :id')
                             ->execute(['id' => $existing]);
                }
                continue;
            }

            if ($existing) {
                $upd = $this->db->prepare(
                    'UPDATE ' . self::TABLE .
                    ' SET date_debut = :deb, date_fin = :fin WHERE id = :id'
                );
                $upd->execute(['deb' => $debut, 'fin' => $fin, 'id' => $existing]);
            } else {
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