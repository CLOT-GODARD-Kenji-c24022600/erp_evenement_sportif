<?php

/**
 * YES - Your Event Solution
 *
 * ERP évènementiel
 *
 * @file Security.php
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.0
 * @since 2026
 */

declare(strict_types=1);

namespace Core;

/**
 * Classe utilitaire de sécurité.
 *
 * Fournit des méthodes pour :
 * - la protection XSS via échappement HTML,
 * - la génération et validation de tokens CSRF,
 * - la sanitisation des entrées utilisateur.
 */
class Security
{
    /** Durée de vie d'un token CSRF en secondes (1h) */
    private const CSRF_TTL = 3600;

    /**
     * Échappe une chaîne pour l'affichage HTML (protection XSS).
     *
     * @param string|null $value Valeur à échapper.
     * @return string Valeur échappée.
     */
    public static function escape(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Génère un token CSRF et le stocke en session.
     *
     * @return string Token CSRF hexadécimal.
     */
    public static function generateCsrfToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token']      = $token;
        $_SESSION['csrf_token_time'] = time();

        return $token;
    }

    /**
     * Vérifie la validité du token CSRF soumis.
     *
     * @param string $submittedToken Token soumis via le formulaire.
     * @return bool Vrai si le token est valide et non expiré.
     */
    public static function validateCsrfToken(string $submittedToken): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $stored  = $_SESSION['csrf_token']      ?? '';
        $created = $_SESSION['csrf_token_time'] ?? 0;

        if (empty($stored) || !hash_equals($stored, $submittedToken)) {
            return false;
        }

        if ((time() - $created) > self::CSRF_TTL) {
            return false;
        }

        // Rotation du token après validation
        unset($_SESSION['csrf_token'], $_SESSION['csrf_token_time']);

        return true;
    }

    /**
     * Sanitise une chaîne de saisie utilisateur (supprime les espaces superflus).
     *
     * @param string|null $value Valeur brute.
     * @return string Valeur nettoyée.
     */
    public static function sanitizeString(?string $value): string
    {
        return trim((string) $value);
    }

    /**
     * Sanitise un entier.
     *
     * @param mixed $value Valeur brute.
     * @return int Valeur entière.
     */
    public static function sanitizeInt(mixed $value): int
    {
        return (int) filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * Valide une adresse email.
     *
     * @param string $email Email à valider.
     * @return bool
     */
    public static function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}
