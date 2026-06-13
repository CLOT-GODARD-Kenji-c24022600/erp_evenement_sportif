<?php

/**
 * YES - Your Event Solution
 * @file DashboardController.php
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 2.1
 * @since 2026
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Models\EventModel;
use App\Models\TodoModel;
use App\Models\ProjectModel;
use App\Models\PlanningModel; // Remplacement ICI
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
                [$pgType, $pgMsg] = ['error', 'Accès refusé : vous ne pouvez pas modifier le planning.'];
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

        // ── Planning (Opérationnel global) ────────────────────
        $planningGlobal = [];
        try {
            $planningGlobal = (new PlanningModel())->getAll();
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

    // ── Planning Global CRUD (redirigé vers PlanningModel) ───

    private function handlePlanningGlobal(): string
    {
        $action = \Core\Security::sanitizeString($_POST['pg_action'] ?? '');
        $model  = new PlanningModel();

        return match($action) {
            'pg_create' => $this->pgCreate($model),
            'pg_update' => $this->pgUpdate($model),
            'pg_delete' => $this->pgDelete($model),
            default     => 'error:Action inconnue.',
        };
    }

    private function pgCreate(PlanningModel $model): string
    {
        $tache = \Core\Security::sanitizeString($_POST['pg_tache'] ?? '');
        if ($tache === '') return 'error:La tâche est obligatoire.';
        
        $ok = $model->create([
            'tache'      => $tache,
            'note'       => \Core\Security::sanitizeString($_POST['pg_note'] ?? ''),
            'statut'     => \Core\Security::sanitizeString($_POST['pg_statut'] ?? 'wip'),
            'date_debut' => !empty($_POST['pg_date_debut']) ? $_POST['pg_date_debut'] : null,
            'date_fin'   => !empty($_POST['pg_date_fin'])   ? $_POST['pg_date_fin']   : null,
            'event_id'   => \Core\Security::sanitizeInt($_POST['pg_event_id'] ?? 0) ?: null,
            'projet_id'  => \Core\Security::sanitizeInt($_POST['pg_projet_id'] ?? 0) ?: null,
            'ordre'      => 0,
            'contact_id' => null, // Peut être géré plus tard depuis le dashboard si besoin
        ]);
        return $ok ? 'success:Tâche ajoutée au planning.' : 'error:Erreur lors de l\'ajout.';
    }

    private function pgUpdate(PlanningModel $model): string
    {
        $id = \Core\Security::sanitizeInt($_POST['pg_id'] ?? 0);
        if (!$id) return 'error:ID invalide.';
        
        $ok = $model->update($id, [
            'tache'      => \Core\Security::sanitizeString($_POST['pg_tache'] ?? ''),
            'note'       => \Core\Security::sanitizeString($_POST['pg_note'] ?? ''),
            'statut'     => \Core\Security::sanitizeString($_POST['pg_statut'] ?? 'wip'),
            'date_debut' => !empty($_POST['pg_date_debut']) ? $_POST['pg_date_debut'] : null,
            'date_fin'   => !empty($_POST['pg_date_fin'])   ? $_POST['pg_date_fin']   : null,
            'event_id'   => \Core\Security::sanitizeInt($_POST['pg_event_id'] ?? 0) ?: null,
            'projet_id'  => \Core\Security::sanitizeInt($_POST['pg_projet_id'] ?? 0) ?: null,
            'ordre'      => 0,
            'contact_id' => null, // Géré depuis l'opérationnel
        ]);
        return $ok ? 'success:Tâche de planning mise à jour.' : 'error:Erreur mise à jour.';
    }

    private function pgDelete(PlanningModel $model): string
    {
        $id = \Core\Security::sanitizeInt($_POST['pg_id'] ?? 0);
        return $id && $model->delete($id)
            ? 'success:Tâche supprimée.' : 'error:Erreur suppression.';
    }
}