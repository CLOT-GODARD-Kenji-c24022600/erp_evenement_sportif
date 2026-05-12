<?php

/**
 * YES - Your Event Solution
 *
 * Point d'entrée pour la création d'un événement.
 * Redirige vers le controller approprié, ne contient aucune logique métier.
 *
 * @file traitement_event.php
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.1
 * @since 2026
 */

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use Core\Session;
use Core\Router;
use App\Models\EventModel;
use App\Controllers\EventController;

Session::start();

if (!Session::has('user_id')) {
    Router::redirect('/login');
}

$envFile = __DIR__ . '/../../../.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        [$key, $value] = array_map('trim', explode('=', $line, 2)) + ['', ''];
        putenv("{$key}={$value}");
    }
}

(new EventController(new EventModel()))->create();