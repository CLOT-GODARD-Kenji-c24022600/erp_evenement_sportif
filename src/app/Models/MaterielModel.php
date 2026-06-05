<?php

/**
 * YES – Your Event Solution
 * @file MaterielModel.php
 * @version 1.1  –  2026
 */

declare(strict_types=1);

namespace App\Models;

use Core\Database;
use PDO;
use PDOException;

class MaterielModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getByEvent(int $eventId): array
    {
        try {
            $stmt = $this->db->prepare(
                'SELECT * FROM materiel WHERE event_id = :id ORDER BY nom ASC'
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
                'SELECT * FROM materiel WHERE projet_id = :id ORDER BY nom ASC'
            );
            $stmt->execute(['id' => $projetId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException) {
            return [];
        }
    }

    /** Totaux budget matériel par événement */
    public function getBudgetTotauxEvent(int $eventId): array
    {
        return $this->getBudgetTotaux('event_id', $eventId);
    }

    /** Totaux budget matériel par projet */
    public function getBudgetTotauxProjet(int $projetId): array
    {
        return $this->getBudgetTotaux('projet_id', $projetId);
    }

    private function getBudgetTotaux(string $col, int $id): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT
                    COALESCE(SUM(CASE WHEN categorie_achat='loue'   THEN budget ELSE 0 END),0) AS total_loue,
                    COALESCE(SUM(CASE WHEN categorie_achat='achete' THEN budget ELSE 0 END),0) AS total_achete,
                    COALESCE(SUM(budget),0) AS total_global
                 FROM materiel WHERE {$col} = :id"
            );
            $stmt->execute(['id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['total_loue' => 0, 'total_achete' => 0, 'total_global' => 0];
        } catch (PDOException) {
            return ['total_loue' => 0, 'total_achete' => 0, 'total_global' => 0];
        }
    }

    public function create(array $d): bool
    {
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO materiel
                    (event_id, projet_id, nom, quantite, fournisseur, date_in, date_out,
                     commentaire, categorie_achat, budget)
                 VALUES
                    (:event_id, :projet_id, :nom, :quantite, :fournisseur, :date_in, :date_out,
                     :commentaire, :categorie_achat, :budget)'
            );
            return $stmt->execute([
                'event_id'        => $d['event_id']        ?? null,
                'projet_id'       => $d['projet_id']       ?? null,
                'nom'             => $d['nom']              ?? '',
                'quantite'        => (float) ($d['quantite'] ?? 1),
                'fournisseur'     => $d['fournisseur']      ?? null,
                'date_in'         => !empty($d['date_in'])  ? $d['date_in']  : null,
                'date_out'        => !empty($d['date_out']) ? $d['date_out'] : null,
                'commentaire'     => $d['commentaire']      ?? null,
                'categorie_achat' => in_array($d['categorie_achat'] ?? '', ['loue','achete'], true) ? $d['categorie_achat'] : '',
                'budget'          => $d['budget'] !== '' && $d['budget'] !== null ? (float) $d['budget'] : null,
            ]);
        } catch (PDOException) {
            return false;
        }
    }

    public function update(int $id, array $d): bool
    {
        try {
            $stmt = $this->db->prepare(
                'UPDATE materiel SET
                    nom=:nom, quantite=:quantite, fournisseur=:fournisseur,
                    date_in=:date_in, date_out=:date_out, commentaire=:commentaire,
                    categorie_achat=:categorie_achat, budget=:budget
                 WHERE id=:id'
            );
            return $stmt->execute([
                'nom'             => $d['nom']              ?? '',
                'quantite'        => (float) ($d['quantite'] ?? 1),
                'fournisseur'     => $d['fournisseur']      ?? null,
                'date_in'         => !empty($d['date_in'])  ? $d['date_in']  : null,
                'date_out'        => !empty($d['date_out']) ? $d['date_out'] : null,
                'commentaire'     => $d['commentaire']      ?? null,
                'categorie_achat' => in_array($d['categorie_achat'] ?? '', ['loue','achete'], true) ? $d['categorie_achat'] : '',
                'budget'          => $d['budget'] !== '' && $d['budget'] !== null ? (float) $d['budget'] : null,
                'id'              => $id,
            ]);
        } catch (PDOException) {
            return false;
        }
    }

    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare('DELETE FROM materiel WHERE id = :id');
            return $stmt->execute(['id' => $id]);
        } catch (PDOException) {
            return false;
        }
    }
}