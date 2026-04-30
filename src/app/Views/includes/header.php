<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
}
// On inclut notre super dictionnaire !
include 'translations.php';
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $t['app_name'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="bg-body-tertiary">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4 shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="/">🏆 <?= $t['app_name'] ?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="/"><?= $t['nav_dashboard'] ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#"><?= $t['nav_directory'] ?></a>
                </li>
                <li class="nav-item ms-2 border-start ps-3">
                    <a class="nav-link text-warning fw-bold" href="/nouvel_event.php"><?= $t['nav_new_event'] ?></a>
                </li>
            </ul>
            
            <div class="d-flex align-items-center gap-2">
                <a href="?lang=fr" class="btn btn-sm <?= $lang === 'fr' ? 'btn-light' : 'btn-outline-light' ?>">FR</a>
                <a href="?lang=en" class="btn btn-sm <?= $lang === 'en' ? 'btn-light' : 'btn-outline-light' ?>">EN</a>
                <button id="darkModeToggle" class="btn btn-sm btn-dark ms-2" title="Thème">🌙</button>
            </div>
        </div>
    </div>
</nav>

<script>
    const toggleBtn = document.getElementById('darkModeToggle');
    const htmlElement = document.documentElement;

    if (localStorage.getItem('theme') === 'dark') {
        htmlElement.setAttribute('data-bs-theme', 'dark');
        toggleBtn.innerText = '☀️';
        toggleBtn.classList.replace('btn-dark', 'btn-light');
    }

    toggleBtn.addEventListener('click', () => {
        if (htmlElement.getAttribute('data-bs-theme') === 'dark') {
            htmlElement.setAttribute('data-bs-theme', 'light');
            localStorage.setItem('theme', 'light');
            toggleBtn.innerText = '🌙';
            toggleBtn.classList.replace('btn-light', 'btn-dark');
        } else {
            htmlElement.setAttribute('data-bs-theme', 'dark');
            localStorage.setItem('theme', 'dark');
            toggleBtn.innerText = '☀️';
            toggleBtn.classList.replace('btn-dark', 'btn-light');
        }
    });
</script>