<?php

/**
 * YES - Your Event Solution
 *
 * ERP évènementiel
 *
 * @file DashboardController.php
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.1
 * @since 2026
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Models\EventModel;
use App\Models\TodoModel;
use App\Models\ProjectModel;
use App\Controllers\TodoController;
use Core\Security;
use Core\Session;

/**
 * Contrôleur du tableau de bord.
 *
 * Charge les données nécessaires à l'affichage du dashboard :
 * liste des événements, statistiques, tâches et projets.
 */
class DashboardController
{
    private EventModel     $eventModel;
    private TodoModel      $todoModel;
    private TodoController $todoController;

    /**
     * @param EventModel     $eventModel     Modèle événements.
     * @param TodoModel      $todoModel      Modèle tâches.
     * @param TodoController $todoController Contrôleur tâches.
     */
    public function __construct(
        EventModel     $eventModel,
        TodoModel      $todoModel,
        TodoController $todoController
    ) {
        $this->eventModel     = $eventModel;
        $this->todoModel      = $todoModel;
        $this->todoController = $todoController;
    }

    /**
     * Prépare toutes les données nécessaires à la vue du dashboard.
     *
     * Traite également les actions POST (todo) avant de charger les données.
     *
     * @return array{
     *   evenements: array,
     *   todos: array,
     *   todoStats: array,
     *   utilisateurs: array,
     *   projets: array,
     *   todoMsg: string|null,
     *   todoType: string,
     *   erreur_bdd: string|null
     * }
     */
    public function index(): array
    {
        // Traitement des actions todo (POST)
        $todoMsg  = null;
        $todoType = 'success';

        $result = $this->todoController->handleRequest();
        if ($result !== null) {
            [$todoType, $todoMsg] = explode(':', $result, 2);
        }

        $eventsPerPage = 6;
        $eventsPage    = max(1, Security::sanitizeInt($_GET['events_page'] ?? 1));

        // Chargement des événements
        $evenements = [];
        $erreurBdd  = null;
        $eventsTotal = 0;
        $eventsPages  = 1;

        try {
            $eventsTotal = $this->eventModel->countAll();
            $eventsPages = max(1, (int) ceil($eventsTotal / $eventsPerPage));
            $eventsPage  = min($eventsPage, $eventsPages);
            $offset      = ($eventsPage - 1) * $eventsPerPage;

            $evenements = $this->eventModel->getPaginated($eventsPerPage, $offset);
        } catch (\Exception $e) {
            $erreurBdd = 'Impossible de charger les événements : ' . $e->getMessage();
        }

        // Chargement des todos et statistiques
        $todos        = [];
        $todoStats    = ['total' => 0, 'done' => 0, 'en_cours' => 0, 'en_attente' => 0];
        $utilisateurs = [];

        try {
            $todos        = $this->todoModel->getAllTodos();
            $todoStats    = $this->todoModel->getStats();
            $utilisateurs = $this->todoModel->getUtilisateurs();
        } catch (\Exception $e) {
            // Table inexistante : migration SQL pas encore jouée
        }

        // Chargement des projets pour les selects de la todolist
        $projets = [];

        try {
            $projets = (new ProjectModel())->getAllSimple();
        } catch (\Exception $e) {
            // Table projets inexistante : migration pas encore jouée
        }

        return [
            'evenements'      => $evenements,
            'eventsPage'      => $eventsPage,
            'eventsPerPage'    => $eventsPerPage,
            'eventsTotal'      => $eventsTotal,
            'eventsTotalPages' => $eventsPages,
            'todos'           => $todos,
            'todoStats'       => $todoStats,
            'utilisateurs'    => $utilisateurs,
            'projets'         => $projets,
            'todoMsg'         => $todoMsg,
            'todoType'        => $todoType,
            'erreur_bdd'      => $erreurBdd,
        ];
    }
}