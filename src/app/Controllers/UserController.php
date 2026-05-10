<?php

/**
 * YES - Your Event Solution
 *
 * ERP évènementiel
 *
 * @file UserController.php
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.0
 * @since 2026
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Models\UserModel;
use Core\Security;
use Core\Session;

/**
 * Contrôleur de gestion des utilisateurs (administration).
 *
 * Permet à un administrateur d'approuver, rejeter, promouvoir,
 * rétrograder ou supprimer des comptes utilisateurs.
 */
class UserController
{
    private UserModel $userModel;

    /**
     * @param UserModel $userModel Modèle utilisateur.
     */
    public function __construct(UserModel $userModel)
    {
        $this->userModel = $userModel;
    }

    /**
     * Retourne tous les utilisateurs (pour la liste admin).
     *
     * @return array[]
     */
    public function getAll(): array
    {
        return $this->userModel->getAll();
    }

    /**
     * Exécute une action administrative sur un utilisateur.
     *
     * @param int    $targetId Identifiant de l'utilisateur cible.
     * @param string $action   Action à effectuer.
     * @return void
     */
    public function handleAction(int $targetId, string $action): void
    {
        $currentUserId = (int) Session::get('user_id', 0);

        // Sécurité : un admin ne peut pas agir sur son propre compte
        if ($targetId === $currentUserId) {
            return;
        }

        $validActions = ['approuver', 'rejeter', 'supprimer', 'promouvoir_admin', 'retrograder_staff'];

        if (!in_array($action, $validActions, true)) {
            return;
        }

        match ($action) {
            'approuver'        => $this->userModel->setStatut($targetId, 'approuve'),
            'rejeter'          => $this->userModel->setStatut($targetId, 'rejete'),
            'supprimer'        => $this->userModel->delete($targetId),
            'promouvoir_admin' => $this->userModel->setRole($targetId, 'admin'),
            'retrograder_staff'=> $this->userModel->setRole($targetId, 'staff'),
        };
    }
}
