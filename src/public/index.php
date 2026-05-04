<?php
// --- 1. INITIALISATION (Ton travail) ---
// On charge l'autoloading de Composer
require_once __DIR__ . '/../../vendor/autoload.php';

// --- BLOC DE TEST DE CONNEXION ---
use Core\Database;

try {
    $db = Database::getConnection();
    $stmt = $db->query("SELECT COUNT(*) as nb FROM projets");
    $res = $stmt->fetch();
    $db_status = "✅ BDD Connectée (" . $res['nb'] . " projets trouvés)";
} catch (\Exception $e) {
    $db_status = "❌ Erreur BDD : " . $e->getMessage();
}
// --- FIN DU BLOC DE TEST ---


// --- 2. ROUTAGE ET SÉCURITÉ (Le travail de ton collègue + tes chemins propres) ---
// On définit le chemin des vues
$viewDir = __DIR__ . '/../app/Views/';

// Définition de la page par défaut ('dashboard' au lieu de 'accueil')
$page = $_GET['page'] ?? 'dashboard';

// Sécurité : Liste blanche des pages autorisées
$pages_autorisees = ['dashboard', 'nouvel_event', 'annuaire'];

// Si l'utilisateur tape une page non autorisée, on force vers le dashboard
if (!in_array($page, $pages_autorisees)) {
    $page = 'dashboard';
}


// --- 3. AFFICHAGE (Le sandwich sémantique) ---
// On inclut le Header
include $viewDir . 'includes/header.php';

// Affichage du statut de la BDD pour le debug (à supprimer plus tard)
echo "<div style='background: #f8f9fa; border-bottom: 1px solid #dee2e6; padding: 5px 15px; font-family: monospace; font-size: 12px;'>$db_status</div>";

// On inclut la page sécurisée
$file = $viewDir . $page . '.php';
if (file_exists($file)) {
    include $file;
} else {
    echo "<section class='py-5 text-center'><h1>404</h1><p>Vue introuvable pour cette page.</p></section>";
}

// On inclut le Footer
include $viewDir . 'includes/footer.php';