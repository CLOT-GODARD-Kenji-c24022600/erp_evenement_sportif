<?php
// On charge l'autoloading de Composer (la magie qui évite les include partout)
require_once __DIR__ . '/../../vendor/autoload.php';

// On définit le chemin des vues
$viewDir = __DIR__ . '/../app/Views/';

// On récupère la page (ex: ?page=projet), sinon 'accueil' par défaut
$page = $_GET['page'] ?? 'accueil';

// Le sandwich sémantique
include $viewDir . 'includes/header.php';

$file = $viewDir . $page . '.php';
if (file_exists($file)) {
    include $file;
} else {
    echo "<section class='py-5 text-center'><h1>404</h1><p>Page introuvable.</p></section>";
}

include $viewDir . 'includes/footer.php';