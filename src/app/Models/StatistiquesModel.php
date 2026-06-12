<?php

/**
 * YES – Your Event Solution
 *
 * @file StatistiquesModel.php
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.1
 * @since 2026
 */

declare(strict_types=1);

namespace App\Models;

use Core\Database;
use PDO;
use PDOException;

class StatistiquesModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // ── KPIs globaux ─────────────────────────────────────────

    public function kpis(): array
    {
        try {
            $k = [];

            $k['nb_evenements']  = (int) $this->db->query('SELECT COUNT(*) FROM evenements')->fetchColumn();
            $k['nb_projets']     = (int) $this->db->query('SELECT COUNT(*) FROM projets')->fetchColumn();
            $k['nb_contacts']    = (int) $this->db->query('SELECT COUNT(*) FROM contacts')->fetchColumn();
            $k['nb_users']       = (int) $this->db->query("SELECT COUNT(*) FROM utilisateurs WHERE statut = 'approuve'")->fetchColumn();

            $k['total_facture']  = (float) $this->db->query(
                'SELECT COALESCE(SUM(prix_unitaire * quantite), 0) FROM facturation'
            )->fetchColumn();

            $k['total_produits'] = (float) $this->db->query(
                "SELECT COALESCE(SUM(previsionnel), 0) FROM budget_lignes WHERE type = 'produit'"
            )->fetchColumn();

            $k['total_charges']  = (float) $this->db->query(
                "SELECT COALESCE(SUM(previsionnel), 0) FROM budget_lignes WHERE type = 'charge'"
            )->fetchColumn();

            // Taux complétion todos — statuts réels : en_attente / en_cours / termine
            $row = $this->db->query(
                "SELECT COUNT(*) AS total, SUM(status = 'termine') AS done FROM todos"
            )->fetch(PDO::FETCH_ASSOC);
            $k['taux_todos'] = ($row && (int) $row['total'] > 0)
                ? round((int) $row['done'] / (int) $row['total'] * 100)
                : 0;

            return $k;
        } catch (PDOException) {
            return [
                'nb_evenements'  => 0, 'nb_projets'    => 0,
                'nb_contacts'    => 0, 'nb_users'      => 0,
                'total_facture'  => 0, 'total_produits' => 0,
                'total_charges'  => 0, 'taux_todos'    => 0,
            ];
        }
    }

    // ── Événements par mois (12 derniers mois) ───────────────

    public function evenementsParMois(): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT DATE_FORMAT(date_debut, '%Y-%m') AS mois, COUNT(*) AS total
                 FROM evenements
                 WHERE date_debut >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                 GROUP BY mois
                 ORDER BY mois ASC"
            );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException) {
            return [];
        }
    }

    // ── Budget produits vs charges par événement (top 8) ─────

    public function budgetParEvenement(): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT
                    e.nom AS label,
                    COALESCE(SUM(CASE WHEN bl.type = 'produit' THEN bl.previsionnel ELSE 0 END), 0) AS produits,
                    COALESCE(SUM(CASE WHEN bl.type = 'charge'  THEN bl.previsionnel ELSE 0 END), 0) AS charges
                 FROM evenements e
                 LEFT JOIN budget_lignes bl ON bl.event_id = e.id
                 GROUP BY e.id, e.nom
                 ORDER BY (produits + charges) DESC
                 LIMIT 8"
            );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException) {
            return [];
        }
    }

    // ── Taux de complétion todos + planning ──────────────────

    public function tauxCompletion(): array
    {
        // Todos — statuts réels BDD : en_attente / en_cours / termine
        try {
            $row = $this->db->query(
                "SELECT
                    SUM(status = 'termine')    AS done,
                    SUM(status = 'en_cours')   AS doing,
                    SUM(status = 'en_attente') AS todo,
                    COUNT(*) AS total
                 FROM todos"
            )->fetch(PDO::FETCH_ASSOC);
            $todos = $row ?: ['done' => 0, 'doing' => 0, 'todo' => 0, 'total' => 0];
        } catch (PDOException) {
            $todos = ['done' => 0, 'doing' => 0, 'todo' => 0, 'total' => 0];
        }

        // Planning lignes — statuts réels BDD : wip / valide / en_cours / annule / …
        try {
            $row2 = $this->db->query(
                "SELECT
                    SUM(statut = 'valide')   AS done,
                    SUM(statut = 'en_cours') AS doing,
                    SUM(statut = 'wip')      AS todo,
                    COUNT(*) AS total
                 FROM planning_lignes"
            )->fetch(PDO::FETCH_ASSOC);
            $planning = $row2 ?: ['done' => 0, 'doing' => 0, 'todo' => 0, 'total' => 0];
        } catch (PDOException) {
            $planning = ['done' => 0, 'doing' => 0, 'todo' => 0, 'total' => 0];
        }

        return compact('todos', 'planning');
    }

    // ── Top 5 prestataires par montant facturé ───────────────

    public function topPrestataires(): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT
                    COALESCE(NULLIF(TRIM(prestataire), ''), 'Sans nom') AS prestataire,
                    SUM(prix_unitaire * quantite) AS total
                 FROM facturation
                 GROUP BY TRIM(prestataire)
                 ORDER BY total DESC
                 LIMIT 5"
            );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException) {
            return [];
        }
    }

    // ── Évolution facturation sur 12 mois ────────────────────

    public function facturationParMois(): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT
                    DATE_FORMAT(created_at, '%Y-%m') AS mois,
                    SUM(prix_unitaire * quantite)    AS total
                 FROM facturation
                 WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                 GROUP BY mois
                 ORDER BY mois ASC"
            );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException) {
            return [];
        }
    }
}