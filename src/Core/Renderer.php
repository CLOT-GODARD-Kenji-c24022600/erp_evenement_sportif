<?php

/**
 * YES - Your Event Solution
 *
 * @file Renderer.php
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.2
 * @since 2026
 */

declare(strict_types=1);

namespace Core;

/**
 * Moteur de rendu des vues.
 *
 * Responsabilités :
 * - Afficher les pages publiques sans layout app.
 * - Afficher les pages authentifiées avec le layout complet
 *   (sidebar + header + vue + footer).
 * - Injecter les variables dans le scope des vues via extract().
 * - Supprimer le FOUC : CSS chargé dans le <head> avant tout HTML visible.
 */
class Renderer
{
    /**
     * Affiche une page publique (sans sidebar ni topbar).
     *
     * @param string               $viewFile Chemin absolu de la vue.
     * @param array<string, mixed> $data     Variables à injecter dans la vue.
     * @param string               $theme    Thème actif ('light' ou 'dark').
     * @param string               $extraJs  Balise script supplémentaire.
     * @return void
     */
    public static function renderPublic(
        string $viewFile,
        array  $data,
        string $theme,
        string $extraJs = ''
    ): void {
        extract($data, EXTR_SKIP);
        ?>
        <!DOCTYPE html>
        <html lang="fr" data-bs-theme="<?= htmlspecialchars($theme, ENT_QUOTES) ?>">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>YES – Your Event Solution</title>
            <link rel="icon" type="image/png" href="assets/img/YES-Your-Event-Solution.png">
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
            <link rel="stylesheet" href="assets/css/auth.css">
            <?php /* Anti-FOUC : cache le body, révèle dès que le DOM est parsé sans attendre le CDN */ ?>
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
            <?php include $viewFile; ?>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
            <?php if ($extraJs !== '') echo $extraJs; ?>
        </body>
        </html>
        <?php
    }

    /**
     * Affiche une page authentifiée avec le layout complet.
     *
     * Structure HTML correcte pour éviter le FOUC :
     * 1. <head> avec TOUS les CSS (Bootstrap + layout + page-specific)
     * 2. <body> caché jusqu'au DOMContentLoaded (sans attendre le CDN)
     * 3. Sidebar + Header + Vue + Footer
     * 4. Scripts JS en fin de body
     *
     * @param string               $viewFile Chemin absolu de la vue principale.
     * @param array<string, mixed> $data     Variables à injecter dans toutes les vues.
     * @param string               $extraCss Balise(s) CSS spécifique(s) à la page.
     * @param string               $extraJs  Balise(s) JS spécifique(s) à la page.
     * @return void
     */
    public static function renderApp(
        string $viewFile,
        array  $data,
        string $extraCss = '',
        string $extraJs  = ''
    ): void {
        extract($data, EXTR_SKIP);

        $layoutDir = __DIR__ . '/../app/Views/layouts/';

        $lang  = $lang  ?? 'fr';
        $theme = $theme ?? 'light';
        ?>
        <!DOCTYPE html>
        <html lang="<?= htmlspecialchars($lang, ENT_QUOTES) ?>" data-bs-theme="<?= htmlspecialchars($theme, ENT_QUOTES) ?>">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?= htmlspecialchars($t['app_name'] ?? 'YES', ENT_QUOTES) ?></title>
            <link rel="icon" type="image/png" href="assets/img/YES-Your-Event-Solution.png">

            <?php /* Bootstrap en premier — base indispensable */ ?>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

            <?php /* CSS global layout — sidebar, topbar, etc. */ ?>
            <link rel="stylesheet" href="assets/css/layout.css">

            <?php /* CSS spécifique à la page (dashboard.css, staff.css, etc.) */ ?>
            <?php if ($extraCss !== '') echo $extraCss . "\n"; ?>

            <?php /* Anti-FOUC : body invisible, révélé dès que le DOM est parsé sans attendre le CDN */ ?>
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
        <body class="<?= (isset($_COOKIE['sidebar']) && $_COOKIE['sidebar'] === 'collapsed') ? 'collapsed' : '' ?>">

        <?php
        include $layoutDir . 'sidebar.php';
        include $layoutDir . 'header.php';
        include $viewFile;
        include $layoutDir . 'footer.php';

        if ($extraJs !== '') {
            echo $extraJs . "\n";
        }
        ?>

        </body>
        </html>
        <?php
    }
}