<?php
// 0. Démarrage de la session
session_start();

// 0 bis. Empêche l'erreur "headers already sent"
ob_start();

// 0 ter. Définit l'heure de Paris pour les tokens de mot de passe
date_default_timezone_set('Europe/Paris');

// --- 1. INITIALISATION ---
require_once __DIR__ . '/../../vendor/autoload.php';

use Core\Database;

try {
    $db = Database::getConnection();
    
    // Test de connexion BDD (Debug)
    $stmt = $db->query("SELECT COUNT(*) as nb FROM projets");
    $res = $stmt->fetch();
    $db_status = "✅ BDD Connectée (" . $res['nb'] . " projets trouvés)";

    // 🚨 LE VIGILE : Vérification continue de l'autorisation 🚨
    if (isset($_SESSION['user_id'])) {
        $stmtCheck = $db->prepare("SELECT statut FROM utilisateurs WHERE id = ?");
        $stmtCheck->execute([$_SESSION['user_id']]);
        $statutActuel = $stmtCheck->fetchColumn();

        if (!$statutActuel || $statutActuel !== 'approuve') {
            session_destroy();
            header("Location: ?page=login");
            exit();
        }
    }

} catch (\Exception $e) {
    $db_status = "❌ Erreur BDD : " . $e->getMessage();
}

// --- 2. ROUTAGE ET SÉCURITÉ ---
$viewDir = __DIR__ . '/../app/Views/';

// Déconnexion rapide
if (isset($_GET['page']) && $_GET['page'] === 'logout') {
    session_destroy();
    header("Location: ?page=login");
    exit();
}

// Pages accessibles sans être connecté
$pages_publiques = ['login', 'inscription', 'forgot_password', 'reset_password'];

// Récupération de la page demandée
$page = $_GET['page'] ?? 'dashboard';

// Si non connecté et page privée -> Redirection login
if (!isset($_SESSION['user_id']) && !in_array($page, $pages_publiques)) {
    $page = 'login';
}

// Liste blanche globale
$pages_autorisees = ['login', 'inscription', 'dashboard', 'nouvel_event', 'annuaire', 'utilisateurs', 'forgot_password', 'reset_password'];

if (!in_array($page, $pages_autorisees)) {
    $page = 'dashboard';
}

// --- 3. AFFICHAGE (100% Sémantique) ---

// On n'affiche le Header que sur les pages privées
if (!in_array($page, $pages_publiques)) {
    include $viewDir . 'includes/header.php';
} else {
    // Header HTML minimal pour les pages publiques (Login, Inscription...)
    echo '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><title>SportERP</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"></head><body class="bg-light">';
}

// Bandeau de debug BDD
echo "<aside style='background: #f8f9fa; border-bottom: 1px solid #dee2e6; padding: 5px 15px; font-family: monospace; font-size: 12px; text-align: center;'>$db_status</aside>";

// Inclusion de la vue
$file = $viewDir . $page . '.php';
if (file_exists($file)) {
    include $file;
} else {
    echo "<section class='py-5 text-center'><h1>404</h1><p>Vue introuvable.</p></section>";
}

// On n'affiche le Footer que sur les pages privées
if (!in_array($page, $pages_publiques)) {
    include $viewDir . 'includes/footer.php';
} else {
    echo '</body></html>';
}