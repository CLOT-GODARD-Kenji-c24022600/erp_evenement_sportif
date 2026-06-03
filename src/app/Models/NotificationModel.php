<?php

/**
 * YES – Your Event Solution
 * @file NotificationModel.php
 * @version 1.0  –  2026
 */

declare(strict_types=1);

namespace App\Models;

use Core\Database;
use PDO;
use PDOException;

class NotificationModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // ── Lecture ───────────────────────────────────────────────

    /** Notifications non lues pour un utilisateur */
    public function getUnread(int $userId): array
    {
        try {
            $stmt = $this->db->prepare(
                'SELECT * FROM notifications
                 WHERE (user_id = :uid OR user_id = 0) AND lu = 0
                 ORDER BY created_at DESC
                 LIMIT 20'
            );
            $stmt->execute(['uid' => $userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException) {
            return [];
        }
    }

    /** Toutes les notifications d'un utilisateur (pour la page dédiée) */
    public function getAll(int $userId, int $limit = 50): array
    {
        try {
            $stmt = $this->db->prepare(
                'SELECT * FROM notifications
                 WHERE user_id = :uid OR user_id = 0
                 ORDER BY created_at DESC
                 LIMIT :limit'
            );
            $stmt->bindValue('uid',   $userId, PDO::PARAM_INT);
            $stmt->bindValue('limit', $limit,  PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException) {
            return [];
        }
    }

    public function countUnread(int $userId): int
    {
        try {
            $stmt = $this->db->prepare(
                'SELECT COUNT(*) FROM notifications
                 WHERE (user_id = :uid OR user_id = 0) AND lu = 0'
            );
            $stmt->execute(['uid' => $userId]);
            return (int) $stmt->fetchColumn();
        } catch (PDOException) {
            return 0;
        }
    }

    // ── Écriture ──────────────────────────────────────────────

    public function create(array $d): bool
    {
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO notifications
                    (user_id, type, titre, message, lien, event_id, projet_id, todo_id)
                 VALUES
                    (:user_id, :type, :titre, :message, :lien, :event_id, :projet_id, :todo_id)'
            );
            return $stmt->execute([
                'user_id'   => (int) ($d['user_id']  ?? 0),
                'type'      => $d['type']      ?? 'info',
                'titre'     => $d['titre']     ?? '',
                'message'   => $d['message']   ?? null,
                'lien'      => $d['lien']      ?? null,
                'event_id'  => !empty($d['event_id'])  ? (int) $d['event_id']  : null,
                'projet_id' => !empty($d['projet_id']) ? (int) $d['projet_id'] : null,
                'todo_id'   => !empty($d['todo_id'])   ? (int) $d['todo_id']   : null,
            ]);
        } catch (PDOException) {
            return false;
        }
    }

    public function markRead(int $id, int $userId): bool
    {
        try {
            $stmt = $this->db->prepare(
                'UPDATE notifications SET lu = 1
                 WHERE id = :id AND (user_id = :uid OR user_id = 0)'
            );
            return $stmt->execute(['id' => $id, 'uid' => $userId]);
        } catch (PDOException) {
            return false;
        }
    }

    public function markAllRead(int $userId): bool
    {
        try {
            $stmt = $this->db->prepare(
                'UPDATE notifications SET lu = 1
                 WHERE (user_id = :uid OR user_id = 0) AND lu = 0'
            );
            return $stmt->execute(['uid' => $userId]);
        } catch (PDOException) {
            return false;
        }
    }

    public function delete(int $id, int $userId): bool
    {
        try {
            $stmt = $this->db->prepare(
                'DELETE FROM notifications WHERE id = :id AND (user_id = :uid OR user_id = 0)'
            );
            return $stmt->execute(['id' => $id, 'uid' => $userId]);
        } catch (PDOException) {
            return false;
        }
    }

    // ── Génération automatique des alertes ───────────────────

    /**
     * Génère toutes les notifications automatiques :
     *  - Tâches en retard
     *  - Événements dans moins de 7 jours
     *  - Budget dépassé
     * Appelé depuis le Router à chaque chargement de page.
     */
    public function generateAuto(int $userId): void
    {
        $this->generateTodosRetard($userId);
        $this->generateEvenementsProches($userId);
        $this->generateBudgetDepasse($userId);
    }

    private function generateTodosRetard(int $userId): void
    {
        try {
            // Tâches en retard assignées à cet utilisateur
            $stmt = $this->db->prepare(
                "SELECT id, title, due_date FROM todos
                 WHERE status != 'termine'
                   AND due_date < CURDATE()
                   AND due_date IS NOT NULL
                   AND assigned_to = :uid"
            );
            $stmt->execute(['uid' => $userId]);
            $todos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($todos as $todo) {
                // Éviter les doublons : vérifier si la notif existe déjà aujourd'hui
                $check = $this->db->prepare(
                    "SELECT id FROM notifications
                     WHERE type = 'todo_retard' AND todo_id = :tid
                       AND user_id = :uid AND DATE(created_at) = CURDATE()"
                );
                $check->execute(['tid' => $todo['id'], 'uid' => $userId]);
                if ($check->fetchColumn()) continue;

                $retard = (int) ceil((time() - strtotime($todo['due_date'])) / 86400);
                $this->create([
                    'user_id' => $userId,
                    'type'    => 'todo_retard',
                    'titre'   => 'Tâche en retard',
                    'message' => "« {$todo['title']} » est en retard de {$retard} jour" . ($retard > 1 ? 's' : '') . '.',
                    'lien'    => '/dashboard',
                    'todo_id' => $todo['id'],
                ]);
            }
        } catch (PDOException) {}
    }

    private function generateEvenementsProches(int $userId): void
    {
        try {
            // Événements dans moins de 7 jours
            $stmt = $this->db->query(
                "SELECT id, nom, date_debut FROM evenements
                 WHERE date_debut BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                 ORDER BY date_debut ASC"
            );
            $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($events as $ev) {
                $check = $this->db->prepare(
                    "SELECT id FROM notifications
                     WHERE type = 'event_proche' AND event_id = :eid
                       AND user_id = :uid AND DATE(created_at) = CURDATE()"
                );
                $check->execute(['eid' => $ev['id'], 'uid' => $userId]);
                if ($check->fetchColumn()) continue;

                $jours = (int) ceil((strtotime($ev['date_debut']) - time()) / 86400);
                $msg   = $jours <= 0
                    ? "« {$ev['nom']} » commence aujourd'hui !"
                    : "« {$ev['nom']} » commence dans {$jours} jour" . ($jours > 1 ? 's' : '') . '.';

                $this->create([
                    'user_id'  => $userId,
                    'type'     => 'event_proche',
                    'titre'    => 'Événement proche',
                    'message'  => $msg,
                    'lien'     => '/operationnel?event_id=' . $ev['id'],
                    'event_id' => $ev['id'],
                ]);
            }
        } catch (PDOException) {}
    }

    private function generateBudgetDepasse(int $userId): void
    {
        try {
            // Projets dont les dépenses dépassent le budget défini
            $stmt = $this->db->query(
                "SELECT p.id, p.nom, p.budget,
                        COALESCE(SUM(CASE WHEN pf.type='depense' THEN pf.montant ELSE 0 END),0) AS total_depenses
                 FROM projets p
                 LEFT JOIN projet_finance pf ON pf.projet_id = p.id
                 WHERE p.budget > 0
                 GROUP BY p.id
                 HAVING total_depenses > p.budget"
            );
            $projets = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($projets as $pr) {
                $check = $this->db->prepare(
                    "SELECT id FROM notifications
                     WHERE type = 'budget_depasse' AND projet_id = :pid
                       AND user_id = :uid AND DATE(created_at) = CURDATE()"
                );
                $check->execute(['pid' => $pr['id'], 'uid' => $userId]);
                if ($check->fetchColumn()) continue;

                $depassement = (float) $pr['total_depenses'] - (float) $pr['budget'];
                $this->create([
                    'user_id'   => $userId,
                    'type'      => 'budget_depasse',
                    'titre'     => 'Budget dépassé',
                    'message'   => "Le projet « {$pr['nom']} » dépasse son budget de "
                                   . number_format($depassement, 2, ',', ' ') . ' €.',
                    'lien'      => '/projet_detail?id=' . $pr['id'],
                    'projet_id' => $pr['id'],
                ]);
            }
        } catch (PDOException) {}
    }
}