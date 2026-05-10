<?php

/**
 * YES - Your Event Solution
 *
 * @file Renderer.php
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.0
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

        include $layoutDir . 'sidebar.php';
        include $layoutDir . 'header.php';

        if ($extraCss !== '') {
            echo $extraCss . "\n";
        }

        include $viewFile;

        if ($extraJs !== '') {
            echo $extraJs . "\n";
        }

        include $layoutDir . 'footer.php';
    }
}