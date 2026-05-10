<?php

/**
 * YES - Your Event Solution
 *
 * ERP évènementiel
 *
 * @file QuickCreateController.php
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.0
 * @since 2026
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Models\QuickCreateModel;
use Core\Security;
use Core\Session;

/**
 * Contrôleur pour la création rapide depuis le header.
 *
 * Gère la création rapide d'un événement, d'un projet ou d'un membre du staff
 * via les modales du header de l'application.
 */
class QuickCreateController
{
    private QuickCreateModel $model;

    /**
     * @param QuickCreateModel $model Modèle de création rapide.
     */
    public function __construct(QuickCreateModel $model)
    {
        $this->model = $model;
    }

    /**
     * Traite la requête de création rapide.
     *
     * @return array{msg: string, type: string}
     */
    public function handle(): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['quick_create'])) {
            return ['msg' => '', 'type' => ''];
        }

        $action    = (string) $_POST['quick_create'];
        $userRole  = (string) Session::get('user_role', '');

        try {
            return match ($action) {
                'event'  => $this->createEvent(),
                'projet' => $this->createProjet(),
                'user'   => $userRole === 'admin' ? $this->createUser() : ['msg' => 'Accès refusé.', 'type' => 'danger'],
                default  => ['msg' => '', 'type' => ''],
            };
        } catch (\PDOException $e) {
            return ['msg' => 'Erreur lors de la création : ' . $e->getMessage(), 'type' => 'danger'];
        }
    }

    /**
     * Crée rapidement un événement.
     *
     * @return array{msg: string, type: string}
     */
    private function createEvent(): array
    {
        $this->model->createEvent([
            'nom'         => Security::sanitizeString($_POST['nom']         ?? ''),
            'description' => Security::sanitizeString($_POST['description'] ?? ''),
            'date_debut'  => Security::sanitizeString($_POST['date_debut']  ?? ''),
            'date_fin'    => Security::sanitizeString($_POST['date_fin']    ?? ''),
            'lieu'        => Security::sanitizeString($_POST['lieu']        ?? ''),
        ]);

        return ['msg' => 'Événement créé avec succès !', 'type' => 'success'];
    }

    /**
     * Crée rapidement un projet.
     *
     * @return array{msg: string, type: string}
     */
    private function createProjet(): array
    {
        $this->model->createProjet([
            'nom'         => Security::sanitizeString($_POST['nom']         ?? ''),
            'description' => Security::sanitizeString($_POST['description'] ?? ''),
        ]);

        return ['msg' => 'Projet créé avec succès !', 'type' => 'success'];
    }

    /**
     * Crée rapidement un membre du staff (admin uniquement).
     *
     * @return array{msg: string, type: string}
     */
    private function createUser(): array
    {
        $hash = password_hash('Bienvenue123!', PASSWORD_DEFAULT);

        $this->model->createUser([
            'prenom'   => Security::sanitizeString($_POST['prenom'] ?? ''),
            'nom'      => Security::sanitizeString($_POST['nom']    ?? ''),
            'email'    => Security::sanitizeString($_POST['email']  ?? ''),
            'password' => $hash,
            'poste'    => Security::sanitizeString($_POST['poste']  ?? ''),
            'role'     => in_array($_POST['role'] ?? '', ['staff', 'admin'], true) ? $_POST['role'] : 'staff',
        ]);

        return ['msg' => 'Membre ajouté ! Mdp provisoire : Bienvenue123!', 'type' => 'success'];
    }
}
