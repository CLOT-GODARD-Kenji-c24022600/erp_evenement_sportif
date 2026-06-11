<?php

/**
 * YES - Your Event Solution
 *
 * @file Permission.php
 * Helper centralisé pour les vérifications de rôle.
 */

declare(strict_types=1);

namespace Core;

use App\Models\UserModel;

class Permission
{
    // ── Groupes de rôles ─────────────────────────────────────

    /** Peut créer/modifier/supprimer des événements */
    public static function canManageEvents(string $role): bool
    {
        return in_array($role, ['super_admin','admin','developpeur','chef_projet'], true);
    }

    /** Peut accéder à l'opérationnel et le modifier */
    public static function canOperationnel(string $role): bool
    {
        return in_array($role, ['super_admin','admin','developpeur','chef_projet','regisseur','commercial'], true);
    }

    /** Peut accéder aux projets */
    public static function canProjects(string $role): bool
    {
        return in_array($role, ['super_admin','admin','developpeur','chef_projet','regisseur','commercial'], true);
    }

    /** Peut accéder à l'annuaire */
    public static function canAnnuaire(string $role): bool
    {
        return in_array($role, ['super_admin','admin','developpeur','chef_projet','regisseur','commercial','staff'], true);
    }

    /** Peut accéder à la page staff */
    public static function canStaffPage(string $role): bool
    {
        return in_array($role, ['super_admin','admin','developpeur','chef_projet','regisseur'], true);
    }

    /** Peut créer/modifier des tâches todo */
    public static function canTodo(string $role): bool
    {
        return in_array($role, ['super_admin','admin','developpeur','chef_projet','regisseur','commercial','staff'], true);
    }

    /** Peut créer/modifier des entrées du planning global (dashboard) */
    public static function canPlanningGlobal(string $role): bool
    {
        return in_array($role, ['super_admin','admin','developpeur','chef_projet','regisseur'], true);
    }

    /** Peut créer rapidement des événements (bouton + Ajouter) */
    public static function canQuickCreateEvent(string $role): bool
    {
        return self::canManageEvents($role);
    }

    /** Peut créer rapidement des projets */
    public static function canQuickCreateProjet(string $role): bool
    {
        return self::canProjects($role);
    }

    /** Accès complet admin */
    public static function isPrivileged(string $role): bool
    {
        return UserModel::isPrivileged($role);
    }

    // ── Helper : récupère le rôle depuis la session ──────────

    public static function currentRole(): string
    {
        return (string) Session::get('user_role', 'staff');
    }

    /** Redirige vers /dashboard si la permission manque */
    public static function requireOr403(bool $allowed): void
    {
        if (!$allowed) {
            http_response_code(403);
            header('Location: /dashboard');
            exit;
        }
    }
}