<?php

/**
 * YES – Your Event Solution
 * @file FacturationModel.php
 * @version 1.0  –  2026
 */

declare(strict_types=1);

namespace App\Models;

use Core\Database;
use PDO;
use PDOException;

class FacturationModel
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
                'SELECT *, (prix_unitaire * quantite) AS total
                 FROM facturation WHERE event_id = :id ORDER BY categorie ASC, id ASC'
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
                'SELECT *, (prix_unitaire * quantite) AS total
                 FROM facturation WHERE projet_id = :id ORDER BY categorie ASC, id ASC'
            );
            $stmt->execute(['id' => $projetId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException) {
            return [];
        }
    }

    public function getTotalEvent(int $eventId): float
    {
        try {
            $stmt = $this->db->prepare(
                'SELECT COALESCE(SUM(prix_unitaire * quantite), 0) FROM facturation WHERE event_id = :id'
            );
            $stmt->execute(['id' => $eventId]);
            return (float) $stmt->fetchColumn();
        } catch (PDOException) {
            return 0.0;
        }
    }

    public function create(array $d): bool
    {
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO facturation
                    (event_id, projet_id, categorie, poste, prestataire, contact,
                     telephone, mail, prix_unitaire, quantite,
                     statut_devis, statut_facture, statut_virement, note)
                 VALUES
                    (:event_id, :projet_id, :categorie, :poste, :prestataire, :contact,
                     :telephone, :mail, :prix_unitaire, :quantite,
                     :statut_devis, :statut_facture, :statut_virement, :note)'
            );
            return $stmt->execute([
                'event_id'        => $d['event_id']      ?? null,
                'projet_id'       => $d['projet_id']     ?? null,
                'categorie'       => $d['categorie']     ?? null,
                'poste'           => $d['poste']         ?? null,
                'prestataire'     => $d['prestataire']   ?? null,
                'contact'         => $d['contact']       ?? null,
                'telephone'       => $d['telephone']     ?? null,
                'mail'            => $d['mail']          ?? null,
                'prix_unitaire'   => (float) ($d['prix_unitaire'] ?? 0),
                'quantite'        => (float) ($d['quantite']      ?? 1),
                'statut_devis'    => (int) ($d['statut_devis']    ?? 0),
                'statut_facture'  => (int) ($d['statut_facture']  ?? 0),
                'statut_virement' => (int) ($d['statut_virement'] ?? 0),
                'note'            => $d['note']          ?? null,
            ]);
        } catch (PDOException) {
            return false;
        }
    }

    public function update(int $id, array $d): bool
    {
        try {
            $stmt = $this->db->prepare(
                'UPDATE facturation SET
                    categorie=:categorie, poste=:poste, prestataire=:prestataire,
                    contact=:contact, telephone=:telephone, mail=:mail,
                    prix_unitaire=:prix_unitaire, quantite=:quantite,
                    statut_devis=:statut_devis, statut_facture=:statut_facture,
                    statut_virement=:statut_virement, note=:note
                 WHERE id=:id'
            );
            return $stmt->execute([
                'categorie'       => $d['categorie']     ?? null,
                'poste'           => $d['poste']         ?? null,
                'prestataire'     => $d['prestataire']   ?? null,
                'contact'         => $d['contact']       ?? null,
                'telephone'       => $d['telephone']     ?? null,
                'mail'            => $d['mail']          ?? null,
                'prix_unitaire'   => (float) ($d['prix_unitaire'] ?? 0),
                'quantite'        => (float) ($d['quantite']      ?? 1),
                'statut_devis'    => (int) ($d['statut_devis']    ?? 0),
                'statut_facture'  => (int) ($d['statut_facture']  ?? 0),
                'statut_virement' => (int) ($d['statut_virement'] ?? 0),
                'note'            => $d['note']          ?? null,
                'id'              => $id,
            ]);
        } catch (PDOException) {
            return false;
        }
    }

    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare('DELETE FROM facturation WHERE id = :id');
            return $stmt->execute(['id' => $id]);
        } catch (PDOException) {
            return false;
        }
    }
}