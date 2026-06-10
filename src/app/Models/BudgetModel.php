<?php

/**
 * YES – Your Event Solution
 * @file BudgetModel.php
 * @version 1.1  –  2026
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

    public function getTotauxProjet(int $projetId): array
    {
        return $this->getTotaux('projet_id', $projetId);
    }

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
                    (projet_id, event_id, type, categorie, sous_categorie, libelle,
                     previsionnel, comparatif, note, fournisseur, sponsor)
                 VALUES
                    (:projet_id, :event_id, :type, :categorie, :sous_categorie, :libelle,
                     :previsionnel, :comparatif, :note, :fournisseur, :sponsor)'
            );
            return $stmt->execute([
                'projet_id'      => $d['projet_id']      ?? null,
                'event_id'       => $d['event_id']       ?? null,
                'type'           => in_array($d['type'] ?? '', ['produit','charge'], true) ? $d['type'] : 'charge',
                'categorie'      => $d['categorie']      ?? '',
                'sous_categorie' => $d['sous_categorie'] ?? '',
                'libelle'        => $d['libelle']        ?? '',
                'previsionnel'   => (float) ($d['previsionnel'] ?? 0),
                'comparatif'     => (float) ($d['comparatif']   ?? 0),
                'note'           => $d['note']           ?? null,
                'fournisseur'    => $d['fournisseur']    ?? null,
                'sponsor'        => $d['sponsor']        ?? null,
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
                    libelle=:libelle, previsionnel=:previsionnel, comparatif=:comparatif,
                    note=:note, fournisseur=:fournisseur, sponsor=:sponsor
                 WHERE id=:id'
            );
            return $stmt->execute([
                'type'           => in_array($d['type'] ?? '', ['produit','charge'], true) ? $d['type'] : 'charge',
                'categorie'      => $d['categorie']      ?? '',
                'sous_categorie' => $d['sous_categorie'] ?? '',
                'libelle'        => $d['libelle']        ?? '',
                'previsionnel'   => (float) ($d['previsionnel'] ?? 0),
                'comparatif'     => (float) ($d['comparatif']   ?? 0),
                'note'           => $d['note']           ?? null,
                'fournisseur'    => $d['fournisseur']    ?? null,
                'sponsor'        => $d['sponsor']        ?? null,
                'id'             => $id,
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

    /**
     * Synchronise une ligne de facturation vers le budget (charges).
     * Crée ou met à jour une ligne de charge liée via source_fact_id.
     * Si $delete=true, supprime la ligne de budget correspondante.
     */
    public function syncFacturationToBudget(
        int $factId,
        float $montant,
        string $categorie,
        string $prestataire,
        ?int $eventId,
        ?int $projetId,
        bool $delete = false
    ): void {
        // Ajouter la colonne si absente
        try {
            $this->db->query('SELECT source_fact_id FROM budget_lignes LIMIT 1');
        } catch (PDOException) {
            try {
                $this->db->exec('ALTER TABLE budget_lignes ADD COLUMN source_fact_id INT DEFAULT NULL');
            } catch (PDOException) {
                return;
            }
        }

        // Chercher une ligne existante liée à cette ligne facturation
        $stmt = $this->db->prepare(
            'SELECT id FROM budget_lignes WHERE source_fact_id = :fid LIMIT 1'
        );
        $stmt->execute(['fid' => $factId]);
        $existingId = $stmt->fetchColumn();

        if ($delete) {
            if ($existingId) {
                $this->db->prepare('DELETE FROM budget_lignes WHERE id = :id')
                         ->execute(['id' => $existingId]);
            }
            return;
        }

        $libelle = trim($prestataire) ?: 'Facturation #' . $factId;
        $cat     = trim($categorie)   ?: 'Facturation';

        if ($existingId) {
            // Mise à jour de la ligne existante
            $this->db->prepare(
                'UPDATE budget_lignes
                 SET libelle = :lib, categorie = :cat, previsionnel = :prev
                 WHERE id = :id'
            )->execute([
                'lib'  => $libelle,
                'cat'  => $cat,
                'prev' => $montant,
                'id'   => $existingId,
            ]);
        } else {
            // Création d'une nouvelle ligne de charge
            $this->db->prepare(
                'INSERT INTO budget_lignes
                    (event_id, projet_id, type, categorie, sous_categorie, libelle,
                     previsionnel, comparatif, note, fournisseur, source_fact_id)
                 VALUES
                    (:eid, :pid, :type, :cat, :scat, :lib,
                     :prev, 0, :note, :four, :fid)'
            )->execute([
                'eid'  => $eventId  ?: null,
                'pid'  => $projetId ?: null,
                'type' => 'charge',
                'cat'  => $cat,
                'scat' => 'Facturation',
                'lib'  => $libelle,
                'prev' => $montant,
                'note' => 'Synchronisé depuis la facturation',
                'four' => $libelle,
                'fid'  => $factId,
            ]);
        }
    }
}