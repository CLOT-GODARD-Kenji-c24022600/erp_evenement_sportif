<?php

/**
 * YES - Your Event Solution
 *
 * @file Bootstrap.php
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.0
 * @since 2026
 */

declare(strict_types=1);

namespace Core;

/**
 * Initialisation de l'application.
 *
 * Responsabilités :
 * - Charger les variables d'environnement (.env)
 * - Démarrer la session sécurisée
 * - Résoudre la langue active
 * - Résoudre le thème actif
 */
class Bootstrap
{
    /**
     * Initialise l'environnement complet de l'application.
     *
     * @return void
     */
    public static function init(): void
    {
        self::loadEnv();
        Session::start();
    }

    /**
     * Charge les variables du fichier .env dans l'environnement PHP.
     *
     * @return void
     */
    private static function loadEnv(): void
    {
        $envFile = __DIR__ . '/../../.env';

        if (!file_exists($envFile)) {
            return;
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (!str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = array_map('trim', explode('=', $line, 2));

            if ($key === '') {
                continue;
            }

            putenv("{$key}={$value}");
            $_ENV[$key]    = $value;
            $_SERVER[$key] = $value;
        }
    }

    /**
     * Retourne la langue active (session ou cookie, défaut 'fr').
     *
     * @return string
     */
    public static function getLang(): string
    {
        $lang = Session::get('lang', $_COOKIE['lang'] ?? 'fr');

        return in_array($lang, ['fr', 'en'], true) ? $lang : 'fr';
    }

    /**
     * Retourne le thème actif (cookie, défaut 'light').
     *
     * @return string
     */
    public static function getTheme(): string
    {
        $theme = $_COOKIE['theme'] ?? 'light';

        return in_array($theme, ['light', 'dark'], true) ? $theme : 'light';
    }

    /**
     * Charge et retourne le tableau de traductions pour la langue donnée.
     *
     * @param string $lang Code de langue ('fr' ou 'en').
     * @return array<string, string>
     */
    public static function loadTranslations(string $lang): array
    {
        $file = __DIR__ . "/../app/Language/{$lang}.php";

        if (!file_exists($file)) {
            $file = __DIR__ . '/../app/Language/fr.php';
        }

        return require $file;
    }
}