<?php

/**
 * YES – Your Event Solution
 * 
 * @file HistoriqueController.php
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 2.1
 * @since 2026
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Models\HistoriqueModel;
use Core\Security;

class HistoriqueController
{
    private HistoriqueModel $model;

    public function __construct()
    {
        $this->model = new HistoriqueModel();
    }

    public function index(): array
    {
        $entite   = Security::sanitizeString($_GET['entite']    ?? '');
        $userId   = Security::sanitizeInt($_GET['user_id']      ?? 0);
        $dateFrom = Security::sanitizeString($_GET['date_from'] ?? '');
        $dateTo   = Security::sanitizeString($_GET['date_to']   ?? '');

        $logs    = $this->model->getLogs(
            $entite   !== '' ? $entite   : null,
            $userId   > 0   ? $userId   : null,
            $dateFrom !== '' ? $dateFrom : null,
            $dateTo   !== '' ? $dateTo   : null
        );
        $users   = $this->model->getUsers();
        $entites = $this->model->getEntites();

        return compact('logs', 'users', 'entites', 'entite', 'userId', 'dateFrom', 'dateTo');
    }
}