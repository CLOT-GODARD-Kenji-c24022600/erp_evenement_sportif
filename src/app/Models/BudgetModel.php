<?php

/**
 * YES – Your Event Solution
 * @file BudgetModel.php
 * @version 1.0  –  2026
 */

declare(strict_types=1);

namespace App\Models;

use Core\Database;
use PDO;
use PDOException;

class BudgetModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // ── Lecture ───────────────────────────────────────────────

    public function getByProjet(int $projetId): array
    {
        try {
            $stmt = $this->db->prepare(
                'SELECT * FROM budget_lignes WHERE projet_id = :id ORDER BY type ASC, categorie ASC, ordre ASC'
            );
            $stmt->execute(['id' => $projetId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException) {
            return [];
        }
    }

    public function getByEvent(int $eventId): array
    {
        try {
            $stmt = $this->db->prepare(
                'SELECT * FROM budget_lignes WHERE event_id = :id ORDER BY type ASC, categorie ASC'
            );
            $stmt->execute(['id' => $eventId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException) {
            return [];
        }
    }

    /** Résumé (totaux) pour un projet */
    public function getTotauxProjet(int $projetId): array
    {
        return $this->getTotaux('projet_id', $projetId);
    }

    /** Résumé (totaux) pour un event */
    public function getTotauxEvent(int $eventId): array
    {
        return $this->getTotaux('event_id', $eventId);
    }

    private function getTotaux(string $col, int $id): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT
                    COALESCE(SUM(CASE WHEN type='produit' THEN previsionnel ELSE 0 END),0) AS total_produits_prev,
                    COALESCE(SUM(CASE WHEN type='charge'  THEN previsionnel ELSE 0 END),0) AS total_charges_prev,
                    COALESCE(SUM(CASE WHEN type='produit' THEN comparatif   ELSE 0 END),0) AS total_produits_comp,
                    COALESCE(SUM(CASE WHEN type='charge'  THEN comparatif   ELSE 0 END),0) AS total_charges_comp
                 FROM budget_lignes WHERE {$col} = :id"
            );
            $stmt->execute(['id' => $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) return $this->emptyTotaux();

            $row['resultat_prev'] = (float)$row['total_produits_prev'] - (float)$row['total_charges_prev'];
            $row['resultat_comp'] = (float)$row['total_produits_comp'] - (float)$row['total_charges_comp'];
            return $row;
        } catch (PDOException) {
            return $this->emptyTotaux();
        }
    }

    private function emptyTotaux(): array
    {
        return [
            'total_produits_prev' => 0, 'total_charges_prev' => 0,
            'total_produits_comp' => 0, 'total_charges_comp' => 0,
            'resultat_prev'       => 0, 'resultat_comp'       => 0,
        ];
    }

    // ── CRUD ──────────────────────────────────────────────────

    public function create(array $d): bool
    {
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO budget_lignes
                    (projet_id, event_id, type, categorie, sous_categorie, libelle, previsionnel, comparatif, note)
                 VALUES
                    (:projet_id, :event_id, :type, :categorie, :sous_categorie, :libelle, :previsionnel, :comparatif, :note)'
            );
            return $stmt->execute([
                'projet_id'     => $d['projet_id']     ?? null,
                'event_id'      => $d['event_id']      ?? null,
                'type'          => in_array($d['type'] ?? '', ['produit','charge'], true) ? $d['type'] : 'charge',
                'categorie'     => $d['categorie']     ?? '',
                'sous_categorie'=> $d['sous_categorie'] ?? '',
                'libelle'       => $d['libelle']       ?? '',
                'previsionnel'  => (float) ($d['previsionnel'] ?? 0),
                'comparatif'    => (float) ($d['comparatif']   ?? 0),
                'note'          => $d['note']          ?? null,
            ]);
        } catch (PDOException) {
            return false;
        }
    }

    public function update(int $id, array $d): bool
    {
        try {
            $stmt = $this->db->prepare(
                'UPDATE budget_lignes SET
                    type=:type, categorie=:categorie, sous_categorie=:sous_categorie,
                    libelle=:libelle, previsionnel=:previsionnel, comparatif=:comparatif, note=:note
                 WHERE id=:id'
            );
            return $stmt->execute([
                'type'          => in_array($d['type'] ?? '', ['produit','charge'], true) ? $d['type'] : 'charge',
                'categorie'     => $d['categorie']     ?? '',
                'sous_categorie'=> $d['sous_categorie'] ?? '',
                'libelle'       => $d['libelle']       ?? '',
                'previsionnel'  => (float) ($d['previsionnel'] ?? 0),
                'comparatif'    => (float) ($d['comparatif']   ?? 0),
                'note'          => $d['note']          ?? null,
                'id'            => $id,
            ]);
        } catch (PDOException) {
            return false;
        }
    }

    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare('DELETE FROM budget_lignes WHERE id = :id');
            return $stmt->execute(['id' => $id]);
        } catch (PDOException) {
            return false;
        }
    }
}