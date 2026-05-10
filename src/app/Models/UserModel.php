<?php

/**
 * YES - Your Event Solution
 *
 * ERP évènementiel
 *
 * @file UserModel.php
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
 * Modèle de gestion des utilisateurs.
 *
 * Fournit toutes les opérations CRUD et métier
 * liées à la table `utilisateurs`.
 */
class UserModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Trouve un utilisateur par son adresse email.
     *
     * @param string $email Adresse email.
     * @return array|null Données de l'utilisateur ou null.
     */
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM utilisateurs WHERE email = :email LIMIT 1'
        );
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        return $user !== false ? $user : null;
    }

    /**
     * Trouve un utilisateur par son identifiant.
     *
     * @param int $id Identifiant de l'utilisateur.
     * @return array|null Données de l'utilisateur ou null.
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM utilisateurs WHERE id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();

        return $user !== false ? $user : null;
    }

    /**
     * Trouve un utilisateur par son token de réinitialisation.
     *
     * @param string $token Token de réinitialisation.
     * @return array|null Données de l'utilisateur ou null.
     */
    public function findByResetToken(string $token): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, reset_expires FROM utilisateurs WHERE reset_token = :token LIMIT 1'
        );
        $stmt->execute(['token' => $token]);
        $user = $stmt->fetch();

        return $user !== false ? $user : null;
    }

    /**
     * Vérifie si un email est déjà utilisé.
     *
     * @param string $email Adresse email.
     * @return bool
     */
    public function emailExists(string $email): bool
    {
        $stmt = $this->db->prepare(
            'SELECT id FROM utilisateurs WHERE email = :email LIMIT 1'
        );
        $stmt->execute(['email' => $email]);

        return $stmt->fetch() !== false;
    }

    /**
     * Crée un nouvel utilisateur avec le statut "en_attente".
     *
     * @param string $nom    Nom de famille.
     * @param string $prenom Prénom.
     * @param string $email  Adresse email.
     * @param string $hash   Mot de passe hashé.
     * @return bool
     */
    public function create(string $nom, string $prenom, string $email, string $hash): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO utilisateurs (email, password, nom, prenom, role, statut)
             VALUES (:email, :password, :nom, :prenom, 'staff', 'en_attente')"
        );

        return $stmt->execute([
            'email'    => $email,
            'password' => $hash,
            'nom'      => $nom,
            'prenom'   => $prenom,
        ]);
    }

    /**
     * Met à jour le token de réinitialisation de mot de passe.
     *
     * @param int    $userId  Identifiant de l'utilisateur.
     * @param string $token   Token généré.
     * @param string $expires Date d'expiration.
     * @return bool
     */
    public function setResetToken(int $userId, string $token, string $expires): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE utilisateurs SET reset_token = :token, reset_expires = :expires WHERE id = :id'
        );

        return $stmt->execute(['token' => $token, 'expires' => $expires, 'id' => $userId]);
    }

    /**
     * Met à jour le mot de passe hashé et efface le token de réinitialisation.
     *
     * @param int    $userId Identifiant de l'utilisateur.
     * @param string $hash   Nouveau mot de passe hashé.
     * @return bool
     */
    public function updatePassword(int $userId, string $hash): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE utilisateurs SET password = :hash, reset_token = NULL, reset_expires = NULL WHERE id = :id'
        );

        return $stmt->execute(['hash' => $hash, 'id' => $userId]);
    }

    /**
     * Met à jour les informations personnelles d'un utilisateur.
     *
     * @param int    $userId         Identifiant.
     * @param string $prenom         Prénom.
     * @param string $nom            Nom.
     * @param string $email          Email.
     * @param string $poste          Poste.
     * @param string $telephone      Téléphone.
     * @param string $statutPresence Statut de présence.
     * @return bool
     */
    public function updateInfo(
        int    $userId,
        string $prenom,
        string $nom,
        string $email,
        string $poste,
        string $telephone,
        string $statutPresence
    ): bool {
        $stmt = $this->db->prepare(
            'UPDATE utilisateurs
             SET prenom = :prenom, nom = :nom, email = :email,
                 poste = :poste, telephone = :telephone, statut_presence = :statut_presence
             WHERE id = :id'
        );

        return $stmt->execute([
            'prenom'          => $prenom,
            'nom'             => $nom,
            'email'           => $email,
            'poste'           => $poste,
            'telephone'       => $telephone,
            'statut_presence' => $statutPresence,
            'id'              => $userId,
        ]);
    }

    /**
     * Met à jour le nom de fichier de l'avatar.
     *
     * @param int    $userId   Identifiant de l'utilisateur.
     * @param string $filename Nom du fichier avatar.
     * @return bool
     */
    public function updateAvatar(int $userId, string $filename): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE utilisateurs SET avatar = :avatar WHERE id = :id'
        );

        return $stmt->execute(['avatar' => $filename, 'id' => $userId]);
    }

    /**
     * Met à jour la dernière activité de l'utilisateur.
     *
     * @param int $userId Identifiant de l'utilisateur.
     * @return void
     */
    public function updateLastActivity(int $userId): void
    {
        $this->db->prepare(
            'UPDATE utilisateurs SET derniere_activite = NOW() WHERE id = :id'
        )->execute(['id' => $userId]);
    }

    /**
     * Retourne tous les utilisateurs triés par identifiant décroissant.
     *
     * @return array[]
     */
    public function getAll(): array
    {
        return $this->db->query(
            'SELECT * FROM utilisateurs ORDER BY id DESC'
        )->fetchAll();
    }

    /**
     * Retourne tous les utilisateurs approuvés (staff actif).
     *
     * @return array[]
     */
    public function getAllApproved(): array
    {
        return $this->db->query(
            "SELECT * FROM utilisateurs WHERE statut = 'approuve' ORDER BY nom ASC, prenom ASC"
        )->fetchAll();
    }

    /**
     * Modifie le statut d'un utilisateur.
     *
     * @param int    $userId Identifiant de l'utilisateur.
     * @param string $statut Nouveau statut ('approuve', 'rejete', 'en_attente').
     * @return bool
     */
    public function setStatut(int $userId, string $statut): bool
    {
        $allowed = ['approuve', 'rejete', 'en_attente'];
        if (!in_array($statut, $allowed, true)) {
            return false;
        }

        $stmt = $this->db->prepare(
            'UPDATE utilisateurs SET statut = :statut WHERE id = :id'
        );

        return $stmt->execute(['statut' => $statut, 'id' => $userId]);
    }

    /**
     * Modifie le rôle d'un utilisateur.
     *
     * @param int    $userId Identifiant de l'utilisateur.
     * @param string $role   Nouveau rôle ('admin', 'staff').
     * @return bool
     */
    public function setRole(int $userId, string $role): bool
    {
        $allowed = ['admin', 'staff'];
        if (!in_array($role, $allowed, true)) {
            return false;
        }

        $stmt = $this->db->prepare(
            'UPDATE utilisateurs SET role = :role WHERE id = :id'
        );

        return $stmt->execute(['role' => $role, 'id' => $userId]);
    }

    /**
     * Supprime un utilisateur.
     *
     * @param int $userId Identifiant de l'utilisateur.
     * @return bool
     */
    public function delete(int $userId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM utilisateurs WHERE id = :id');

        return $stmt->execute(['id' => $userId]);
    }

    /**
     * Vérifie le statut actuel d'un utilisateur.
     *
     * @param int $userId Identifiant de l'utilisateur.
     * @return string|null Statut ou null si introuvable.
     */
    public function getStatut(int $userId): ?string
    {
        $stmt = $this->db->prepare(
            'SELECT statut FROM utilisateurs WHERE id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $userId]);
        $value = $stmt->fetchColumn();

        return $value !== false ? (string) $value : null;
    }
}
