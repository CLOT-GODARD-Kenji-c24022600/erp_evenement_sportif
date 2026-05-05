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
    
    // Test de connexion BDD (Ton précieux debug)
    $stmt = $db->query("SELECT COUNT(*) as nb FROM projets");
    $res = $stmt->fetch();
    $db_status = "✅ BDD Connectée (" . $res['nb'] . " projets trouvés)";

    // 🚨 LE VIGILE : Toujours là pour surveiller les bannis 🚨
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

// --- GESTION DU CHANGEMENT DE LANGUE ---
if (isset($_GET['page']) && $_GET['page'] === 'change_lang') {
    if (isset($_GET['lang']) && in_array($_GET['lang'], ['fr', 'en'])) {
        $_SESSION['lang'] = $_GET['lang'];
    }
    // On redirige vers la page d'où l'utilisateur venait (ou dashboard par défaut)
    $return_page = $_GET['return'] ?? 'dashboard';
    header("Location: index.php?page=" . urlencode($return_page));
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

// Liste blanche globale (Toutes tes pages sont là + change_lang)
$pages_autorisees = ['login', 'inscription', 'dashboard', 'nouvel_event', 'annuaire', 'utilisateurs', 'forgot_password', 'reset_password', 'profil', 'staff', 'recherche', 'change_lang'];

if (!in_array($page, $pages_autorisees)) {
    $page = 'dashboard';
}

// --- 3. AFFICHAGE (LE NOUVEAU LAYOUT EN L) ---

if (in_array($page, $pages_publiques)) {
    // --- MODE PUBLIC (Login, etc.) : Pas de Sidebar ---
    echo '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><title>SportERP</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"></head><body class="bg-light d-flex align-items-center justify-content-center min-vh-100">';
    
    // On garde quand même le debug discret
    echo "<aside style='position:fixed; top:0; width:100%; background: #f8f9fa; border-bottom: 1px solid #dee2e6; padding: 5px; font-family: monospace; font-size: 10px; text-align: center;'>$db_status</aside>";

    $file = $viewDir . $page . '.php';
    if (file_exists($file)) include $file;
    
    echo '</body></html>';

} else {
    // --- MODE APPLI PRO (Le gros morceau) ---
    
    // 1. On charge le header.php (qui contient <head>, <body> et la barre du haut)
    include $viewDir . 'includes/header.php';

    // 2. Structure Flexbox pour coller la Sidebar à gauche
    echo '<div class="d-flex w-100">';
        
        // 3. Inclusion de la Sidebar (Elle monte tout en haut car elle est dans le d-flex)
        include $viewDir . 'includes/sidebar.php';
        
        // 4. Colonne de droite (Contenu principal)
        echo '<div class="flex-grow-1 d-flex flex-column" style="min-width: 0;">';
            
            echo '<main class="p-4 flex-grow-1">';
                // Ton bandeau de debug BDD, mais formaté proprement
                echo "<div class='text-end mb-3'><span class='badge bg-body border text-muted' style='font-size: 0.65rem;'>$db_status</span></div>";

                // Inclusion de la page (Dashboard, Annuaire...)
                $file = $viewDir . $page . '.php';
                if (file_exists($file)) {
                    include $file;
                } else {
                    echo "<section class='py-5 text-center'><h1>404</h1><p>Vue introuvable.</p></section>";
                }
            echo '</main>';

            // 5. Inclusion du Footer (uniquement dans la zone de droite !)
            include $viewDir . 'includes/footer.php';

        echo '</div>'; // Fin flex-column droite
    echo '</div>'; // Fin d-flex wrapper
}