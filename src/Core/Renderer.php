<?php

/**
 * YES - Your Event Solution
 *
 * @file Renderer.php
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.3
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
 * - Rendu partiel JSON pour les requêtes AJAX SPA (X-Requested-With).
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
     * Affiche une page authentifiée avec le layout complet,
     * ou retourne un JSON partiel si c'est une requête AJAX SPA.
     *
     * Mode SPA (header X-Requested-With: XMLHttpRequest) :
     *   → JSON { html, page, extraCss, extraJs }
     *
     * Mode normal :
     *   → HTML complet avec <head>, sidebar, header, vue, footer.
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
        // ── Mode SPA : requête AJAX → rendu partiel JSON ──────────────────
        if (
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        ) {
            self::renderPartial($viewFile, $data, $extraCss, $extraJs);
            return;
        }

        // ── Mode normal : rendu HTML complet ──────────────────────────────
        extract($data, EXTR_SKIP);

        $layoutDir = __DIR__ . '/../app/Views/layouts/';
        $lang      = $lang  ?? 'fr';
        $theme     = $theme ?? 'light';
        ?>
        <!DOCTYPE html>
        <html lang="<?= htmlspecialchars($lang, ENT_QUOTES) ?>" data-bs-theme="<?= htmlspecialchars($theme, ENT_QUOTES) ?>">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?= htmlspecialchars($t['app_name'] ?? 'YES', ENT_QUOTES) ?></title>
            <link rel="icon" type="image/png" href="assets/img/YES-Your-Event-Solution.png">

            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
            <link rel="stylesheet" href="assets/css/layout.css">

            <?php if ($extraCss !== '') echo $extraCss . "\n"; ?>

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
        ?>

        <div id="spa-content">
        <?php
        include $viewFile;
        include $layoutDir . 'footer.php';

        if ($extraJs !== '') {
            echo $extraJs . "\n";
        }
        ?>
        </div>

        </body>
        </html>
        <?php
    }

    /**
     * Rendu partiel JSON pour le router SPA.
     * Capture uniquement le contenu de la vue + footer via ob_start().
     *
     * @param string               $viewFile Chemin absolu de la vue.
     * @param array<string, mixed> $data     Variables injectées.
     * @param string               $extraCss Balise CSS page-specific.
     * @param string               $extraJs  Balise JS page-specific.
     * @return void
     */
    private static function renderPartial(
        string $viewFile,
        array  $data,
        string $extraCss,
        string $extraJs
    ): void {
        extract($data, EXTR_SKIP);

        $layoutDir = __DIR__ . '/../app/Views/layouts/';

        // Capture le HTML de la vue + footer
        ob_start();
        include $viewFile;
        include $layoutDir . 'footer.php';
        $html = ob_get_clean();

        header('Content-Type: application/json; charset=UTF-8');

        echo json_encode([
            'html'     => $html,
            'page'     => $page ?? '',
            'extraCss' => $extraCss,
            'extraJs'  => $extraJs,
        ], JSON_UNESCAPED_UNICODE);
    }
}