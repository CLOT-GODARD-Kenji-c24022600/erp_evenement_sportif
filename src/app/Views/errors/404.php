<?php

/**
 * YES - Your Event Solution
 *
 * Vue : Page d'erreur 404 – Ressource introuvable.
 *
 * @file 404.php
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.0
 * @since 2026
 *
 * Variables attendues :
 * @var array  $t     Traductions chargées.
 * @var string $theme Thème actif ('light' ou 'dark').
 */

declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="fr" data-bs-theme="<?= htmlspecialchars($theme, ENT_QUOTES) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 – <?= htmlspecialchars($t['app_name'] ?? 'YES', ENT_QUOTES) ?></title>
    <link rel="icon" type="image/png" href="/assets/img/YES-Your-Event-Solution.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/404.css">
    <style>body { visibility: hidden; }</style>
    <script>
        (function () {
            function show() { document.body.classList.add('ready'); }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', show);
            } else {
                show();
            }
        })();
    </script>
    <style>body.ready { visibility: visible; }</style>
</head>
<body class="bg-body-tertiary">

    <main class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
        <section class="text-center error-card">

            <header class="mb-4">
                <figure class="error-code" aria-hidden="true">404</figure>
                <h1 class="h3 fw-bold text-primary mt-2">
                    <?= htmlspecialchars($t['error_404_title'] ?? 'Page introuvable', ENT_QUOTES) ?>
                </h1>
            </header>

            <p class="text-muted mb-4">
                <?= htmlspecialchars($t['error_404_desc'] ?? 'La page que vous cherchez n\'existe pas ou a été déplacée.', ENT_QUOTES) ?>
            </p>

            <nav aria-label="Retour à l'application">
                <a href="/dashboard" class="btn btn-primary px-4 me-2">
                    <i class="bi bi-house-door me-1" aria-hidden="true"></i>
                    <?= htmlspecialchars($t['error_404_home'] ?? 'Accueil', ENT_QUOTES) ?>
                </a>
                <button type="button" class="btn btn-outline-secondary px-4" id="btn-back">
                    <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>
                    <?= htmlspecialchars($t['error_404_back'] ?? 'Retour', ENT_QUOTES) ?>
                </button>
            </nav>

            <footer class="mt-5 text-muted small">
                🏆 <?= htmlspecialchars($t['app_name'] ?? 'YES – Your Event Solution', ENT_QUOTES) ?>
            </footer>

        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/404.js"></script>
</body>
</html>