<?php

/**
 * YES - Your Event Solution
 *
 * ERP évènementiel
 *
 * @file Session.php
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.0
 * @since 2026
 */

declare(strict_types=1);

namespace Core;

/**
 * Gestionnaire de session sécurisé.
 *
 * Centralise le démarrage, la lecture, l'écriture et la destruction
 * des sessions PHP avec des paramètres de sécurité renforcés.
 */
class Session
{
    /**
     * Démarre la session avec des options de sécurité renforcées.
     *
     * @return void
     */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'secure'   => false, // Passer à true en HTTPS
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_start();
    }

    /**
     * Détruit la session courante de manière sécurisée.
     *
     * @return void
     */
    public static function destroy(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    /**
     * Lit une valeur en session.
     *
     * @param string $key     Clé de session.
     * @param mixed  $default Valeur par défaut si la clé n'existe pas.
     * @return mixed
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Écrit une valeur en session.
     *
     * @param string $key   Clé de session.
     * @param mixed  $value Valeur à stocker.
     * @return void
     */
    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Supprime une clé de session.
     *
     * @param string $key Clé à supprimer.
     * @return void
     */
    public static function delete(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Vérifie si une clé existe en session.
     *
     * @param string $key Clé à vérifier.
     * @return bool
     */
    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Retourne et supprime un message flash de la session.
     *
     * @param string $key Clé du message flash.
     * @return string|null
     */
    public static function flash(string $key): ?string
    {
        $value = $_SESSION[$key] ?? null;
        unset($_SESSION[$key]);
        return $value;
    }
}
