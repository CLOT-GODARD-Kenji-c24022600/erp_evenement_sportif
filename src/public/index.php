<?php
// On charge l'autoloading de Composer
require_once __DIR__ . '/../../vendor/autoload.php';

// --- BLOC DE TEST DE CONNEXION ---
use Core\Database;

try {
    $db = Database::getConnection();
    // On tente de compter les projets en base
    $stmt = $db->query("SELECT COUNT(*) as nb FROM projets");
    $res = $stmt->fetch();
    $db_status = "✅ BDD Connectée (" . $res['nb'] . " projets trouvés)";
} catch (\Exception $e) {
    $db_status = "❌ Erreur BDD : " . $e->getMessage();
}
// --- FIN DU BLOC DE TEST ---

// On définit le chemin des vues
$viewDir = __DIR__ . '/../app/Views/';

// On récupère la page (ex: ?page=projet), sinon 'accueil' par défaut
$page = $_GET['page'] ?? 'accueil';

// Le sandwich sémantique
include $viewDir . 'includes/header.php';

// Affichage du statut de la BDD pour le debug (tu pourras le supprimer plus tard)
echo "<div style='background: #f8f9fa; border-bottom: 1px solid #dee2e6; padding: 5px 15px; font-family: monospace; font-size: 12px;'>$db_status</div>";

$file = $viewDir . $page . '.php';
if (file_exists($file)) {
    include $file;
} else {
    echo "<section class='py-5 text-center'><h1>404</h1><p>Page introuvable.</p></section>";
}

include $viewDir . 'includes/footer.php';