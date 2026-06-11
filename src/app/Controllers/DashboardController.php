<?php

/**
 * YES - Your Event Solution
 * @file DashboardController.php
 * @version 1.2  –  2026
 * Ajout : Planning global (PlanningGlobalModel)
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Models\EventModel;
use App\Models\TodoModel;
use App\Models\ProjectModel;
use App\Models\PlanningGlobalModel;
use App\Controllers\TodoController;
use Core\Permission;

class DashboardController
{
    private EventModel     $eventModel;
    private TodoModel      $todoModel;
    private TodoController $todoController;

    public function __construct(
        EventModel     $eventModel,
        TodoModel      $todoModel,
        TodoController $todoController
    ) {
        $this->eventModel     = $eventModel;
        $this->todoModel      = $todoModel;
        $this->todoController = $todoController;
    }

    public function index(): array
    {
        // ── Traitement actions todo (POST) ────────────────────
        $todoMsg  = null;
        $todoType = 'success';

        $result = $this->todoController->handleRequest();
        if ($result !== null) {
            [$todoType, $todoMsg] = explode(':', $result, 2);
        }

        // ── Traitement actions planning global (POST) ─────────
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['pg_action'])) {
            if (Permission::canPlanningGlobal(Permission::currentRole())) {
                [$pgType, $pgMsg] = explode(':', $this->handlePlanningGlobal(), 2);
            } else {
                [$pgType, $pgMsg] = ['error', 'Accès refusé : vous ne pouvez pas modifier le planning global.'];
            }
            if (!$todoMsg) {
                $todoMsg  = $pgMsg;
                $todoType = $pgType;
            }
        }

        // ── Événements ────────────────────────────────────────
        $evenements = [];
        $erreurBdd  = null;
        try {
            $evenements = $this->eventModel->getAll();
        } catch (\Exception $e) {
            $erreurBdd = 'Impossible de charger les événements : ' . $e->getMessage();
        }

        // ── Todos ─────────────────────────────────────────────
        $todos        = [];
        $todoStats    = ['total' => 0, 'done' => 0, 'en_cours' => 0, 'en_attente' => 0];
        $utilisateurs = [];
        try {
            $todos        = $this->todoModel->getAllTodos();
            $todoStats    = $this->todoModel->getStats();
            $utilisateurs = $this->todoModel->getUtilisateurs();
        } catch (\Exception $e) {}

        // ── Projets ───────────────────────────────────────────
        $projets = [];
        try {
            $projets = (new ProjectModel())->getAllSimple();
        } catch (\Exception $e) {}

        // ── Planning global ───────────────────────────────────
        $planningGlobal = [];
        try {
            $planningGlobal = (new PlanningGlobalModel())->getAll();
        } catch (\Exception $e) {}

        $role = Permission::currentRole();
        return [
            'evenements'          => $evenements,
            'todos'               => $todos,
            'todoStats'           => $todoStats,
            'utilisateurs'        => $utilisateurs,
            'projets'             => $projets,
            'planningGlobal'      => $planningGlobal,
            'todoMsg'             => $todoMsg,
            'todoType'            => $todoType,
            'erreur_bdd'          => $erreurBdd,
            'canTodo'             => Permission::canTodo($role),
            'canPlanningGlobal'   => Permission::canPlanningGlobal($role),
            'canManageEvents'     => Permission::canManageEvents($role),
        ];
    }

    // ── Planning Global CRUD ──────────────────────────────────

    private function handlePlanningGlobal(): string
    {
        $action = \Core\Security::sanitizeString($_POST['pg_action'] ?? '');
        $model  = new PlanningGlobalModel();

        return match($action) {
            'pg_create' => $this->pgCreate($model),
            'pg_update' => $this->pgUpdate($model),
            'pg_delete' => $this->pgDelete($model),
            default     => 'error:Action inconnue.',
        };
    }

    private function pgCreate(PlanningGlobalModel $model): string
    {
        $titre = \Core\Security::sanitizeString($_POST['pg_titre'] ?? '');
        if ($titre === '') return 'error:Le titre est obligatoire.';
        $ok = $model->create([
            'titre'       => $titre,
            'description' => \Core\Security::sanitizeString($_POST['pg_description'] ?? ''),
            'couleur'     => \Core\Security::sanitizeString($_POST['pg_couleur']     ?? '#0d6efd'),
            'date_debut'  => $_POST['pg_date_debut'] ?? '',
            'date_fin'    => $_POST['pg_date_fin']   ?? '',
            'statut'      => \Core\Security::sanitizeString($_POST['pg_statut']      ?? 'wip'),
            'event_id'    => \Core\Security::sanitizeInt($_POST['pg_event_id']   ?? 0) ?: null,
            'projet_id'   => \Core\Security::sanitizeInt($_POST['pg_projet_id']  ?? 0) ?: null,
        ]);
        return $ok ? 'success:Entrée planning global ajoutée.' : 'error:Erreur lors de l\'ajout.';
    }

    private function pgUpdate(PlanningGlobalModel $model): string
    {
        $id = \Core\Security::sanitizeInt($_POST['pg_id'] ?? 0);
        if (!$id) return 'error:ID invalide.';
        $ok = $model->update($id, [
            'titre'       => \Core\Security::sanitizeString($_POST['pg_titre']       ?? ''),
            'description' => \Core\Security::sanitizeString($_POST['pg_description'] ?? ''),
            'couleur'     => \Core\Security::sanitizeString($_POST['pg_couleur']     ?? '#0d6efd'),
            'date_debut'  => $_POST['pg_date_debut'] ?? '',
            'date_fin'    => $_POST['pg_date_fin']   ?? '',
            'statut'      => \Core\Security::sanitizeString($_POST['pg_statut']      ?? 'wip'),
            'event_id'    => \Core\Security::sanitizeInt($_POST['pg_event_id']   ?? 0) ?: null,
            'projet_id'   => \Core\Security::sanitizeInt($_POST['pg_projet_id']  ?? 0) ?: null,
        ]);
        return $ok ? 'success:Planning global mis à jour.' : 'error:Erreur mise à jour.';
    }

    private function pgDelete(PlanningGlobalModel $model): string
    {
        $id = \Core\Security::sanitizeInt($_POST['pg_id'] ?? 0);
        return $id && $model->delete($id)
            ? 'success:Entrée supprimée.' : 'error:Erreur suppression.';
    }
}