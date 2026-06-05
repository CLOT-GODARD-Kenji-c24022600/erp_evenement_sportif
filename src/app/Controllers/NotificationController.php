<?php

/**
 * YES – Your Event Solution
 * @file NotificationController.php
 * @version 1.0  –  2026
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Models\NotificationModel;
use Core\Security;
use Core\Session;

class NotificationController
{
    private NotificationModel $model;

    public function __construct()
    {
        $this->model = new NotificationModel();
    }

    /** Appelé en AJAX depuis le header pour récupérer les notifs */
    public function ajaxGet(int $userId): void
    {
        header('Content-Type: application/json; charset=UTF-8');

        $notifs = $this->model->getUnread($userId);
        $count  = $this->model->countUnread($userId);

        echo json_encode([
            'count'         => $count,
            'notifications' => $notifs,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /** Marquer une notification comme lue */
    public function ajaxMarkRead(int $userId): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        $id = Security::sanitizeInt($_POST['id'] ?? 0);
        $ok = $id ? $this->model->markRead($id, $userId) : false;
        echo json_encode(['ok' => $ok]);
        exit;
    }

    /** Marquer toutes comme lues */
    public function ajaxMarkAllRead(int $userId): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        $this->model->markAllRead($userId);
        echo json_encode(['ok' => true]);
        exit;
    }

    /** Supprimer une notification */
    public function ajaxDelete(int $userId): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        $id = Security::sanitizeInt($_POST['id'] ?? 0);
        $ok = $id ? $this->model->delete($id, $userId) : false;
        echo json_encode(['ok' => $ok]);
        exit;
    }
}