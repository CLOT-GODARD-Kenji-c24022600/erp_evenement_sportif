<?php
// On récupère la langue demandée (fr par défaut)
$lang = $_SESSION['lang'] ?? 'fr';

// On définit le chemin vers le dossier Language
// __DIR__ pointe vers src/app/Views/includes/
$langFile = __DIR__ . '/../../Language/' . $lang . '.php';

// Si le fichier existe, on le charge, sinon on charge le français par sécurité
if (file_exists($langFile)) {
    $t = require $langFile;
} else {
    $t = require __DIR__ . '/../../Language/fr.php';
}
?>