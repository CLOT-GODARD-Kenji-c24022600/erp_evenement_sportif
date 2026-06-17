<?php

/**
 * YES - Your Event Solution
 *
 * @file TodoController.php
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.2
 * @since 2026
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Models\TodoModel;
use App\Models\HistoriqueModel;
use Core\Permission;
use Core\Security;
use Core\Session;

class TodoController
{
    private TodoModel $model;

    public function __construct(TodoModel $model)
    {
        $this->model = $model;
    }

    public function handleRequest(): ?string
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['todo_action'])) {
            return null;
        }

        // Vérifier la permission
        if (!Permission::canTodo(Permission::currentRole())) {
            return 'error:Accès refusé.';
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

    private function create(int $userId): string
    {
        $title = Security::sanitizeString($_POST['title'] ?? '');

        if ($title === '') {
            return 'error:Le titre est obligatoire.';
        }

        $eventId  = !empty($_POST['event_id'])  ? Security::sanitizeInt($_POST['event_id'])  : null;
        $projetId = !empty($_POST['projet_id']) ? Security::sanitizeInt($_POST['projet_id']) : null;

        $ok = $this->model->create([
            'title'       => $title,
            'description' => Security::sanitizeString($_POST['description'] ?? ''),
            'category'    => $_POST['category']    ?? 'general',
            'priority'    => Security::sanitizeInt($_POST['priority']    ?? 1),
            'due_date'    => !empty($_POST['due_date'])    ? $_POST['due_date']                           : null,
            'event_id'    => $eventId,
            'projet_id'   => $projetId,
            'assigned_to' => !empty($_POST['assigned_to']) ? Security::sanitizeInt($_POST['assigned_to']) : null,
            'status'      => $_POST['status'] ?? 'en_attente',
            'created_by'  => $userId,
        ]);

        if ($ok) {
            $newId = $this->model->getLastInsertId();
            HistoriqueModel::log('create', 'todo', $newId, "Création de la tâche Todo : {$title}", ['event_id' => $eventId, 'projet_id' => $projetId]);
            return 'success:Tâche créée avec succès !';
        }
        return 'error:Erreur lors de la création.';
    }

    private function setStatus(): string
    {
        $id     = Security::sanitizeInt($_POST['todo_id'] ?? 0);
        $status = Security::sanitizeString($_POST['status'] ?? '');

        if (!$id || $status === '') {
            return 'error:Données invalides.';
        }

        $ok = $this->model->setStatus($id, $status);

        if ($ok) {
            $todo = $this->model->findById($id);
            $eid  = $todo ? ($todo['event_id'] ? (int)$todo['event_id'] : null) : null;
            $pid  = $todo ? ($todo['projet_id'] ? (int)$todo['projet_id'] : null) : null;
            HistoriqueModel::log('set_status', 'todo', $id, "Changement de statut à '{$status}'", ['event_id' => $eid, 'projet_id' => $pid]);
            return 'success:Statut mis à jour.';
        }
        return 'error:Erreur lors du changement de statut.';
    }

    private function delete(): string
    {
        $id = Security::sanitizeInt($_POST['todo_id'] ?? 0);

        if (!$id) {
            return 'error:Identifiant invalide.';
        }

        $todo  = $this->model->findById($id);
        $title = $todo ? $todo['title'] : '';
        $eid   = $todo ? ($todo['event_id'] ? (int)$todo['event_id'] : null) : null;
        $pid   = $todo ? ($todo['projet_id'] ? (int)$todo['projet_id'] : null) : null;

        if ($this->model->delete($id)) {
            HistoriqueModel::log('delete', 'todo', $id, "Suppression de la tâche Todo : {$title}", ['event_id' => $eid, 'projet_id' => $pid]);
            return 'success:Tâche supprimée.';
        }
        return 'error:Erreur lors de la suppression.';
    }

    private function edit(): string
    {
        $id    = Security::sanitizeInt($_POST['todo_id'] ?? 0);
        $title = Security::sanitizeString($_POST['title'] ?? '');

        if (!$id || $title === '') {
            return 'error:Données invalides.';
        }

        $eventId  = !empty($_POST['event_id'])  ? Security::sanitizeInt($_POST['event_id'])  : null;
        $projetId = !empty($_POST['projet_id']) ? Security::sanitizeInt($_POST['projet_id']) : null;

        $ok = $this->model->update($id, [
            'title'       => $title,
            'description' => Security::sanitizeString($_POST['description'] ?? ''),
            'category'    => $_POST['category']    ?? 'general',
            'priority'    => Security::sanitizeInt($_POST['priority']    ?? 1),
            'due_date'    => !empty($_POST['due_date'])    ? $_POST['due_date']                           : null,
            'event_id'    => $eventId,
            'projet_id'   => $projetId,
            'assigned_to' => !empty($_POST['assigned_to']) ? Security::sanitizeInt($_POST['assigned_to']) : null,
            'status'      => $_POST['status'] ?? 'en_attente',
        ]);

        if ($ok) {
            HistoriqueModel::log('update', 'todo', $id, "Modification de la tâche Todo : {$title}", ['event_id' => $eventId, 'projet_id' => $projetId]);
            return 'success:Tâche modifiée avec succès.';
        }
        return 'error:Erreur lors de la modification.';
    }
}