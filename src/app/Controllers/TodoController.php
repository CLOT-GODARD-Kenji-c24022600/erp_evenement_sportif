<?php

namespace App\Controllers;

use App\Models\TodoModel;

class TodoController
{
    private TodoModel $model;

    public function __construct()
    {
        $this->model = new TodoModel();
    }

    public function handleRequest(): ?string
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['todo_action'])) {
            return null;
        }

        $action = $_POST['todo_action'];
        $userId = (int)($_SESSION['user_id'] ?? 0);

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
        $title = trim($_POST['title'] ?? '');
        if ($title === '') {
            return 'error:Le titre est obligatoire.';
        }

        $this->model->create([
            'title'       => $title,
            'description' => trim($_POST['description'] ?? ''),
            'category'    => $_POST['category']    ?? 'general',
            'priority'    => (int)($_POST['priority']    ?? 1),
            'due_date'    => !empty($_POST['due_date'])    ? $_POST['due_date']           : null,
            'event_id'    => !empty($_POST['event_id'])    ? (int)$_POST['event_id']      : null,
            'assigned_to' => !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to']   : null,
            'status'      => $_POST['status'] ?? 'en_attente',
            'created_by'  => $userId,
        ]);

        return 'success:Tache creee avec succes !';
    }

    private function setStatus(): string
    {
        $id     = (int)($_POST['todo_id'] ?? 0);
        $status = $_POST['status'] ?? '';

        if (!$id || $status === '') {
            return 'error:Donnees invalides.';
        }

        $this->model->setStatus($id, $status);
        return 'success:Statut mis a jour.';
    }

    private function delete(): string
    {
        $id = (int)($_POST['todo_id'] ?? 0);
        if (!$id) {
            return 'error:Identifiant invalide.';
        }
        $this->model->delete($id);
        return 'success:Tache supprimee.';
    }

    private function edit(): string
    {
        $id    = (int)($_POST['todo_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');

        if (!$id || $title === '') {
            return 'error:Donnees invalides.';
        }

        $this->model->update($id, [
            'title'       => $title,
            'description' => trim($_POST['description'] ?? ''),
            'category'    => $_POST['category']    ?? 'general',
            'priority'    => (int)($_POST['priority']    ?? 1),
            'due_date'    => !empty($_POST['due_date'])    ? $_POST['due_date']           : null,
            'event_id'    => !empty($_POST['event_id'])    ? (int)$_POST['event_id']      : null,
            'assigned_to' => !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to']   : null,
            'status'      => $_POST['status'] ?? 'en_attente',
        ]);

        return 'success:Tache modifiee avec succes.';
    }
}