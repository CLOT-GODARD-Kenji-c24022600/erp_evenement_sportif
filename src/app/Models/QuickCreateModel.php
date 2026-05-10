<?php

/**
 * YES - Your Event Solution
 *
 * ERP évènementiel
 *
 * @file QuickCreateModel.php
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.0
 * @since 2026
 */

declare(strict_types=1);

namespace App\Models;

use Core\Database;
use PDO;

/**
 * Modèle de création rapide (header).
 *
 * Gère les insertions rapides d'événements, projets et membres
 * depuis les modales du header.
 */
class QuickCreateModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Crée rapidement un événement.
     *
     * @param array $data Données de l'événement.
     * @return bool
     */
    public function createEvent(array $data): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO evenements (nom, description, date_debut, date_fin, lieu)
             VALUES (:nom, :description, :date_debut, :date_fin, :lieu)'
        );

        return $stmt->execute([
            'nom'         => $data['nom'],
            'description' => $data['description'] ?? null,
            'date_debut'  => $data['date_debut'],
            'date_fin'    => $data['date_fin']    ?? null,
            'lieu'        => $data['lieu']        ?? null,
        ]);
    }

    /**
     * Crée rapidement un projet.
     *
     * @param array $data Données du projet.
     * @return bool
     */
    public function createProjet(array $data): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO projets (nom, description, date_creation) VALUES (:nom, :description, NOW())'
        );

        return $stmt->execute([
            'nom'         => $data['nom'],
            'description' => $data['description'] ?? null,
        ]);
    }

    /**
     * Crée rapidement un membre du staff.
     *
     * @param array $data Données du membre.
     * @return bool
     */
    public function createUser(array $data): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO utilisateurs (prenom, nom, email, password, poste, role, statut)
             VALUES (:prenom, :nom, :email, :password, :poste, :role, 'approuve')"
        );

        return $stmt->execute([
            'prenom'   => $data['prenom'],
            'nom'      => $data['nom'],
            'email'    => $data['email'],
            'password' => $data['password'],
            'poste'    => $data['poste'] ?? null,
            'role'     => $data['role']  ?? 'staff',
        ]);
    }
}
