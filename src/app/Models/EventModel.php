<?php

/**
 * YES - Your Event Solution
 *
 * ERP évènementiel
 *
 * @file EventModel.php
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
 * Modèle de gestion des événements sportifs.
 *
 * Fournit les opérations CRUD sur la table `evenements`.
 */
class EventModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Retourne tous les événements triés par date de début.
     *
     * @return array[]
     */
    public function getAll(): array
    {
        return $this->db->query(
            'SELECT * FROM evenements ORDER BY date_debut ASC'
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retourne les prochains événements (à venir).
     *
     * @param int $limit Nombre d'événements à retourner.
     * @return array[]
     */
    public function getUpcoming(int $limit = 3): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, nom, date_debut FROM evenements
             WHERE date_debut >= CURRENT_DATE
             ORDER BY date_debut ASC
             LIMIT :limit'
        );
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Trouve un événement par son identifiant.
     *
     * @param int $id Identifiant de l'événement.
     * @return array|null Données de l'événement ou null.
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM evenements WHERE id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        return $event !== false ? $event : null;
    }

    /**
     * Crée un nouvel événement.
     *
     * @param array $data Données de l'événement.
     * @return bool
     */
    public function create(array $data): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO evenements (projet_id, nom, sport, date_debut, date_fin, lieu, capacite, description)
             VALUES (:projet_id, :nom, :sport, :date_debut, :date_fin, :lieu, :capacite, :description)'
        );

        return $stmt->execute([
            'projet_id'   => $data['projet_id']   ?? 1,
            'nom'         => $data['nom'],
            'sport'       => $data['sport']        ?? null,
            'date_debut'  => $data['date_debut'],
            'date_fin'    => $data['date_fin']     ?? null,
            'lieu'        => $data['lieu']         ?? null,
            'capacite'    => $data['capacite']     ?? null,
            'description' => $data['description']  ?? null,
        ]);
    }

    /**
     * Met à jour un événement existant.
     *
     * @param int   $id   Identifiant de l'événement.
     * @param array $data Nouvelles données.
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE evenements
             SET nom         = :nom,
                 sport       = :sport,
                 date_debut  = :date_debut,
                 date_fin    = :date_fin,
                 lieu        = :lieu,
                 capacite    = :capacite,
                 description = :description
             WHERE id = :id'
        );

        return $stmt->execute([
            'nom'         => $data['nom'],
            'sport'       => $data['sport']        ?? null,
            'date_debut'  => $data['date_debut'],
            'date_fin'    => $data['date_fin']     ?? null,
            'lieu'        => $data['lieu']         ?? null,
            'capacite'    => $data['capacite']     ?? null,
            'description' => $data['description']  ?? null,
            'id'          => $id,
        ]);
    }

    /**
     * Supprime un événement.
     *
     * @param int $id Identifiant de l'événement.
     * @return bool
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM evenements WHERE id = :id');

        return $stmt->execute(['id' => $id]);
    }
}
