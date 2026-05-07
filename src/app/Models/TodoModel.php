<?php

namespace App\Models;

use Core\Database;
use PDO;

class TodoModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /** Recupere toutes les taches avec createur et assigne */
    public function getAllTodos(): array
    {
        $stmt = $this->db->query(
            "SELECT t.*,
                    u.prenom   AS createur_prenom,
                    u.nom      AS createur_nom,
                    a.prenom   AS assigne_prenom,
                    a.nom      AS assigne_nom
             FROM todos t
             LEFT JOIN utilisateurs u ON t.created_by  = u.id
             LEFT JOIN utilisateurs a ON t.assigned_to = a.id
             ORDER BY
                FIELD(t.status, 'en_cours', 'en_attente', 'termine'),
                t.priority DESC,
                t.created_at ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Statistiques globales */
    public function getStats(): array
    {
        $stmt = $this->db->query(
            "SELECT
                COUNT(*)                          AS total,
                SUM(status = 'termine')           AS done,
                SUM(status = 'en_cours')          AS en_cours,
                SUM(status = 'en_attente')        AS en_attente
             FROM todos"
        );
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return [
            'total'      => (int)($row['total']      ?? 0),
            'done'       => (int)($row['done']       ?? 0),
            'en_cours'   => (int)($row['en_cours']   ?? 0),
            'en_attente' => (int)($row['en_attente'] ?? 0),
        ];
    }

    /** Liste des utilisateurs approuves pour le select */
    public function getUtilisateurs(): array
    {
        $stmt = $this->db->query(
            "SELECT id, prenom, nom FROM utilisateurs
             WHERE statut = 'approuve'
             ORDER BY prenom ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Cree une nouvelle tache */
    public function create(array $data): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO todos
                (title, description, category, priority, due_date, event_id, created_by, assigned_to, status, created_at)
             VALUES
                (:title, :description, :category, :priority, :due_date, :event_id, :created_by, :assigned_to, :status, NOW())"
        );
        return $stmt->execute([
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'category'    => $data['category']    ?? 'general',
            'priority'    => $data['priority']    ?? 1,
            'due_date'    => $data['due_date']    ?? null,
            'event_id'    => $data['event_id']    ?? null,
            'created_by'  => $data['created_by'],
            'assigned_to' => $data['assigned_to'] ?? null,
            'status'      => $data['status']      ?? 'en_attente',
        ]);
    }

    /** Change le statut d'une tache */
    public function setStatus(int $id, string $status): bool
    {
        $allowed = ['en_attente', 'en_cours', 'termine'];
        if (!in_array($status, $allowed, true)) {
            return false;
        }
        $stmt = $this->db->prepare(
            "UPDATE todos SET status = ? WHERE id = ?"
        );
        return $stmt->execute([$status, $id]);
    }

    /** Supprime une tache */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM todos WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /** Modifie une tache */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE todos
             SET title       = :title,
                 description = :description,
                 category    = :category,
                 priority    = :priority,
                 due_date    = :due_date,
                 event_id    = :event_id,
                 assigned_to = :assigned_to,
                 status      = :status
             WHERE id = :id"
        );
        return $stmt->execute([
            'id'          => $id,
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'category'    => $data['category']    ?? 'general',
            'priority'    => $data['priority']    ?? 1,
            'due_date'    => $data['due_date']    ?? null,
            'event_id'    => $data['event_id']    ?? null,
            'assigned_to' => $data['assigned_to'] ?? null,
            'status'      => $data['status']      ?? 'en_attente',
        ]);
    }
}