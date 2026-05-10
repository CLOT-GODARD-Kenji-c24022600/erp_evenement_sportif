<?php

/**
 * YES - Your Event Solution
 *
 * ERP évènementiel
 *
 * @file TodoController.php
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.0
 * @since 2026
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Models\TodoModel;
use Core\Security;
use Core\Session;

/**
 * Contrôleur de gestion des tâches (Todo).
 *
 * Dispatche les actions POST : création, modification de statut,
 * édition complète et suppression d'une tâche.
 */
class TodoController
{
    private TodoModel $model;

    /**
     * @param TodoModel $model Modèle tâches.
     */
    public function __construct(TodoModel $model)
    {
        $this->model = $model;
    }

    /**
     * Traite la requête POST en cours et dispatche vers la bonne action.
     *
     * @return string|null Résultat au format "type:message", ou null si rien à traiter.
     */
    public function handleRequest(): ?string
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['todo_action'])) {
            return null;
        }

        $action = (string) $_POST['todo_action'];
        $userId = (int) Session::get('user_id', 0);

        return match ($action) {
            'create'     => $this->create($userId),
            'set_status' => $this->setStatus(),
            'delete'     => $this->delete(),
            'edit'       => $this->edit(),
            default      => null,
        };
    }

    /**
     * Crée une nouvelle tâche.
     *
     * @param int $userId Identifiant de l'utilisateur créateur.
     * @return string Résultat au format "type:message".
     */
    private function create(int $userId): string
    {
        $title = Security::sanitizeString($_POST['title'] ?? '');

        if ($title === '') {
            return 'error:Le titre est obligatoire.';
        }

        $this->model->create([
            'title'       => $title,
            'description' => Security::sanitizeString($_POST['description'] ?? ''),
            'category'    => $_POST['category']    ?? 'general',
            'priority'    => Security::sanitizeInt($_POST['priority']    ?? 1),
            'due_date'    => !empty($_POST['due_date'])    ? $_POST['due_date']                       : null,
            'event_id'    => !empty($_POST['event_id'])    ? Security::sanitizeInt($_POST['event_id']) : null,
            'assigned_to' => !empty($_POST['assigned_to']) ? Security::sanitizeInt($_POST['assigned_to']) : null,
            'status'      => $_POST['status'] ?? 'en_attente',
            'created_by'  => $userId,
        ]);

        return 'success:Tâche créée avec succès !';
    }

    /**
     * Change le statut d'une tâche.
     *
     * @return string Résultat au format "type:message".
     */
    private function setStatus(): string
    {
        $id     = Security::sanitizeInt($_POST['todo_id'] ?? 0);
        $status = Security::sanitizeString($_POST['status'] ?? '');

        if (!$id || $status === '') {
            return 'error:Données invalides.';
        }

        $this->model->setStatus($id, $status);

        return 'success:Statut mis à jour.';
    }

    /**
     * Supprime une tâche.
     *
     * @return string Résultat au format "type:message".
     */
    private function delete(): string
    {
        $id = Security::sanitizeInt($_POST['todo_id'] ?? 0);

        if (!$id) {
            return 'error:Identifiant invalide.';
        }

        $this->model->delete($id);

        return 'success:Tâche supprimée.';
    }

    /**
     * Modifie une tâche existante.
     *
     * @return string Résultat au format "type:message".
     */
    private function edit(): string
    {
        $id    = Security::sanitizeInt($_POST['todo_id'] ?? 0);
        $title = Security::sanitizeString($_POST['title'] ?? '');

        if (!$id || $title === '') {
            return 'error:Données invalides.';
        }

        $this->model->update($id, [
            'title'       => $title,
            'description' => Security::sanitizeString($_POST['description'] ?? ''),
            'category'    => $_POST['category']    ?? 'general',
            'priority'    => Security::sanitizeInt($_POST['priority']    ?? 1),
            'due_date'    => !empty($_POST['due_date'])    ? $_POST['due_date']                       : null,
            'event_id'    => !empty($_POST['event_id'])    ? Security::sanitizeInt($_POST['event_id']) : null,
            'assigned_to' => !empty($_POST['assigned_to']) ? Security::sanitizeInt($_POST['assigned_to']) : null,
            'status'      => $_POST['status'] ?? 'en_attente',
        ]);

        return 'success:Tâche modifiée avec succès.';
    }
}
