<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
}
// On inclut notre super dictionnaire centralisé
$lang = $_SESSION['lang'] ?? 'fr';
$langFile = __DIR__ . '/../../Language/' . $lang . '.php';
$t = file_exists($langFile) ? require $langFile : require __DIR__ . '/../../Language/fr.php';

$currentPage = $_GET['page'] ?? 'dashboard';

// NOUVEAU : On lit le choix du thème dans le cookie (clair par défaut)
$theme = $_COOKIE['theme'] ?? 'light';
?>
<!DOCTYPE html>
<!-- NOUVEAU : On applique le thème généré par PHP directement dans le HTML -->
<html lang="<?= $lang ?>" data-bs-theme="<?= $theme ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $t['app_name'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="bg-body-tertiary d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4 shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="/?page=dashboard">🏆 <?= $t['app_name'] ?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'dashboard' ? 'active' : '' ?>" href="/?page=dashboard"><?= $t['nav_dashboard'] ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'annuaire' ? 'active' : '' ?>" href="/?page=annuaire"><?= $t['nav_directory'] ?></a>
                </li>
                <li class="nav-item ms-2 border-start ps-3">
                    <a class="nav-link text-warning fw-bold <?= $currentPage === 'nouvel_event' ? 'active' : '' ?>" href="/?page=nouvel_event"><?= $t['nav_new_event'] ?></a>
                </li>
            </ul>
            
            <div class="d-flex align-items-center gap-2">
                <a href="?page=<?= $currentPage ?>&lang=fr" class="btn btn-sm <?= $lang === 'fr' ? 'btn-light' : 'btn-outline-light' ?>">FR</a>
                <a href="?page=<?= $currentPage ?>&lang=en" class="btn btn-sm <?= $lang === 'en' ? 'btn-light' : 'btn-outline-light' ?>">EN</a>
                
                <!-- NOUVEAU : Le bouton s'affiche directement avec la bonne icône et couleur grâce à PHP -->
                <button id="darkModeToggle" class="btn btn-sm <?= $theme === 'dark' ? 'btn-light' : 'btn-dark' ?> ms-2" title="Thème">
                    <?= $theme === 'dark' ? '☀️' : '🌙' ?>
                </button>
            </div>
        </div>
    </div>
</nav>

<script>
    const toggleBtn = document.getElementById('darkModeToggle');
    const htmlElement = document.documentElement;

    toggleBtn.addEventListener('click', () => {
        // On détermine le nouveau thème
        let newTheme = htmlElement.getAttribute('data-bs-theme') === 'light' ? 'dark' : 'light';

        // 1. Mise à jour immédiate du HTML (pour l'effet instantané)
        htmlElement.setAttribute('data-bs-theme', newTheme);
        
        // 2. Mise à jour du bouton
        if (newTheme === 'dark') {
            toggleBtn.innerText = '☀️';
            toggleBtn.classList.replace('btn-dark', 'btn-light');
        } else {
            toggleBtn.innerText = '🌙';
            toggleBtn.classList.replace('btn-light', 'btn-dark');
        }

        // 3. Sauvegarde dans un Cookie (pour que PHP puisse le lire au prochain rechargement)
        // max-age=31536000 veut dire qu'on le garde en mémoire pendant 1 an.
        document.cookie = "theme=" + newTheme + "; max-age=31536000; path=/";
    });
</script>