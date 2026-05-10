<?php

/**
 * YES - Your Event Solution
 *
 * Point d'entrée unique de l'application.
 * Ne contient aucune logique métier, aucun HTML, aucun traitement.
 *
 * @file index.php
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.0
 * @since 2026
 */

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

Core\Bootstrap::init();
Core\Router::dispatch();