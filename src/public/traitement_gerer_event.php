<?php

/**
 * YES - Your Event Solution
 *
 * Point d'entrée pour la mise à jour / suppression d'un événement.
 * Redirige vers le controller approprié, ne contient aucune logique métier.
 *
 * @file traitement_gerer_event.php
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

// Cherche le .env en remontant jusqu'à 4 niveaux
$envLoaded = false;
$dir = __DIR__;
for ($i = 0; $i < 4; $i++) {
    $dir = dirname($dir);
    $envFile = $dir . '/.env';
    if (file_exists($envFile)) {
        foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if (str_starts_with(trim($line), '#')) continue;
            $parts = explode('=', $line, 2);
            if (count($parts) === 2) {
                [$key, $value] = array_map('trim', $parts);
                if ($key !== '') putenv("{$key}={$value}");
            }
        }
        $envLoaded = true;
        break;
    }
}

(new EventController(new EventModel()))->handleUpdate();