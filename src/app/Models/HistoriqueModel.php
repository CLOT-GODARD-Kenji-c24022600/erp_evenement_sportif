<?php

/**
 * YES – Your Event Solution
 * 
 * @file HistoriqueModel.php
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 2.1
 * @since 2026
 */

declare(strict_types=1);

namespace App\Models;

use Core\Database;
use Core\Session;
use PDO;
use PDOException;

class HistoriqueModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // ── Écriture ───────────────────────────────────────────────

    public static function log(
        string $action,
        string $entite,
        int    $entiteId,
        string $entiteLabel,
        array  $details = []
    ): void {
        try {
            $db      = Database::getConnection();
            $userId  = (int) (Session::get('user_id') ?? 0);
            $userNom = (string) (Session::get('user_nom') ?? 'Système');
            $ip      = self::getClientIp();

            $stmt = $db->prepare(
                'INSERT INTO historique
                 (user_id, user_nom, action, entite, entite_id, entite_label, details, ip, created_at)
                 VALUES (:user_id, :user_nom, :action, :entite, :entite_id, :entite_label, :details, :ip, NOW())'
            );
            $stmt->execute([
                'user_id'      => $userId,
                'user_nom'     => $userNom,
                'action'       => $action,
                'entite'       => $entite,
                'entite_id'    => $entiteId,
                'entite_label' => $entiteLabel,
                'details'      => json_encode($details, JSON_UNESCAPED_UNICODE),
                'ip'           => $ip,
            ]);
        } catch (\Throwable) {
            // On n'interrompt jamais le flux principal pour un log
        }
    }

    // ── Lecture ───────────────────────────────────────────────

    public function getLogs(
        ?string $entite   = null,
        ?int    $userId   = null,
        ?string $dateFrom = null,
        ?string $dateTo   = null,
        int     $limit    = 200
    ): array {
        try {
            $where  = [];
            $params = [];

            if ($entite !== null && $entite !== '') {
                $where[]          = 'entite = :entite';
                $params['entite'] = $entite;
            }
            if ($userId !== null && $userId > 0) {
                $where[]             = 'user_id = :user_id';
                $params['user_id']   = $userId;
            }
            if ($dateFrom !== null && $dateFrom !== '') {
                $where[]              = 'DATE(created_at) >= :date_from';
                $params['date_from']  = $dateFrom;
            }
            if ($dateTo !== null && $dateTo !== '') {
                $where[]            = 'DATE(created_at) <= :date_to';
                $params['date_to']  = $dateTo;
            }

            $sql = 'SELECT * FROM historique';
            if ($where) {
                $sql .= ' WHERE ' . implode(' AND ', $where);
            }
            $sql .= ' ORDER BY created_at DESC LIMIT :limit';

            $stmt = $this->db->prepare($sql);
            foreach ($params as $k => $v) {
                $stmt->bindValue($k, $v);
            }
            $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException) {
            return [];
        }
    }

    public function getUsers(): array
    {
        try {
            return $this->db->query(
                'SELECT DISTINCT user_id, user_nom FROM historique ORDER BY user_nom ASC'
            )->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException) {
            return [];
        }
    }

    public function getEntites(): array
    {
        try {
            return $this->db->query(
                'SELECT DISTINCT entite FROM historique ORDER BY entite ASC'
            )->fetchAll(PDO::FETCH_COLUMN, 0);
        } catch (PDOException) {
            return [];
        }
    }

    private static function getClientIp(): string
    {
        foreach (['HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'] as $key) {
            if (!empty($_SERVER[$key])) {
                return explode(',', $_SERVER[$key])[0];
            }
        }
        return '0.0.0.0';
    }
}