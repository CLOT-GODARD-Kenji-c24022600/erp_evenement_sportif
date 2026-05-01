<?php
// src/public/index.php

// 1. Définition de la page par défaut
$page = $_GET['page'] ?? 'dashboard';

// 2. Sécurité : Liste blanche des pages autorisées
$pages_autorisees = ['dashboard', 'nouvel_event', 'annuaire'];

// Si l'utilisateur tape une page qui n'existe pas, on le renvoie vers le dashboard
if (!in_array($page, $pages_autorisees)) {
    $page = 'dashboard';
}

// 3. On inclut le Header (une seule fois pour tout le site !)
include '../app/Views/includes/header.php';

// 4. On inclut le contenu spécifique de la page demandée
include "../app/Views/{$page}.php";

// 5. On inclut le Footer (une seule fois pour tout le site !)
include '../app/Views/includes/footer.php';