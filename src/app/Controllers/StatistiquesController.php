<?php

/**
 * YES – Your Event Solution
 *
 * @file StatistiquesController.php
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.1
 * @since 2026
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Models\StatistiquesModel;
use Core\Permission;

class StatistiquesController
{
    private StatistiquesModel $model;

    public function __construct()
    {
        $this->model = new StatistiquesModel();
    }

    /**
     * Page /statistiques — toutes les données passées à la vue PHP.
     * Le JS les lit depuis le bloc <script id="stats-data" type="application/json">.
     */
    public function index(): array
    {
        return [
            'kpis'              => $this->model->kpis(),
            'evenementsParMois' => $this->model->evenementsParMois(),
            'budgetParEvent'    => $this->model->budgetParEvenement(),
            'tauxCompletion'    => $this->model->tauxCompletion(),
            'topPrestataires'   => $this->model->topPrestataires(),
            'factParMois'       => $this->model->facturationParMois(),
        ];
    }

    /**
     * Endpoint AJAX /statistiques/ajax — rafraîchissement sans rechargement de page.
     */
    public function ajax(): void
    {
        if (!Permission::isPrivileged(Permission::currentRole())) {
            http_response_code(403);
            echo json_encode(['error' => 'Accès refusé']);
            exit();
        }

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($this->index(), JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
        exit();
    }
}