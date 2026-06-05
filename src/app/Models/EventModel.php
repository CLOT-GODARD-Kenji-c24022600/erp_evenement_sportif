<?php

/**
 * YES - Your Event Solution
 *
 * ERP évènementiel
 *
 * @file EventModel.php
 * @version 1.2  –  2026
 */

declare(strict_types=1);

namespace App\Models;

use Core\Database;
use PDO;

class EventModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAll(): array
    {
        return $this->db->query(
            'SELECT * FROM evenements ORDER BY date_debut ASC'
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUpcoming(int $limit = 3): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, nom, date_debut FROM evenements
             WHERE date_debut >= CURRENT_DATE
             ORDER BY date_debut ASC
             LIMIT :limit'
        );
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM evenements WHERE id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        return $event !== false ? $event : null;
    }

    public function create(array $data): bool
    {
        $projetId = isset($data['projet_id']) && $data['projet_id'] !== ''
                    ? (int) $data['projet_id']
                    : null;

        $stmt = $this->db->prepare(
            'INSERT INTO evenements
                (projet_id, nom, sport, date_debut, date_fin, lieu, capacite, description,
                 date_preprod_debut, date_preprod_fin,
                 date_prod_debut, date_prod_fin,
                 date_exploit_debut, date_exploit_fin,
                 date_demontage_debut, date_demontage_fin,
                 drive_url, drive_doc_url, maps_url)
             VALUES
                (:projet_id, :nom, :sport, :date_debut, :date_fin, :lieu, :capacite, :description,
                 :date_preprod_debut, :date_preprod_fin,
                 :date_prod_debut, :date_prod_fin,
                 :date_exploit_debut, :date_exploit_fin,
                 :date_demontage_debut, :date_demontage_fin,
                 :drive_url, :drive_doc_url, :maps_url)'
        );

        $stmt->bindValue(':projet_id',            $projetId,                           $projetId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':nom',                  $data['nom'],                        PDO::PARAM_STR);
        $stmt->bindValue(':sport',                $data['sport'] ?? null,              PDO::PARAM_STR);
        $stmt->bindValue(':date_debut',           $data['date_debut'],                 PDO::PARAM_STR);
        $stmt->bindValue(':date_fin',             $data['date_fin'] ?? null,           PDO::PARAM_STR);
        $stmt->bindValue(':lieu',                 $data['lieu'] ?? null,               PDO::PARAM_STR);
        $stmt->bindValue(':capacite',             $data['capacite'] ?? null,           $data['capacite'] === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':description',          $data['description'] ?? null,        PDO::PARAM_STR);
        $stmt->bindValue(':date_preprod_debut',   $data['date_preprod_debut']   ?: null, PDO::PARAM_STR);
        $stmt->bindValue(':date_preprod_fin',     $data['date_preprod_fin']     ?: null, PDO::PARAM_STR);
        $stmt->bindValue(':date_prod_debut',      $data['date_prod_debut']      ?: null, PDO::PARAM_STR);
        $stmt->bindValue(':date_prod_fin',        $data['date_prod_fin']        ?: null, PDO::PARAM_STR);
        $stmt->bindValue(':date_exploit_debut',   $data['date_exploit_debut']   ?: null, PDO::PARAM_STR);
        $stmt->bindValue(':date_exploit_fin',     $data['date_exploit_fin']     ?: null, PDO::PARAM_STR);
        $stmt->bindValue(':date_demontage_debut', $data['date_demontage_debut'] ?: null, PDO::PARAM_STR);
        $stmt->bindValue(':date_demontage_fin',   $data['date_demontage_fin']   ?: null, PDO::PARAM_STR);
        $stmt->bindValue(':drive_url',            $data['drive_url']            ?: null, PDO::PARAM_STR);
        $stmt->bindValue(':drive_doc_url',        $data['drive_doc_url']        ?: null, PDO::PARAM_STR);
        $stmt->bindValue(':maps_url',             $data['maps_url']             ?: null, PDO::PARAM_STR);

        return $stmt->execute();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE evenements SET
                nom                  = :nom,
                sport                = :sport,
                date_debut           = :date_debut,
                date_fin             = :date_fin,
                lieu                 = :lieu,
                capacite             = :capacite,
                description          = :description,
                date_preprod_debut   = :date_preprod_debut,
                date_preprod_fin     = :date_preprod_fin,
                date_prod_debut      = :date_prod_debut,
                date_prod_fin        = :date_prod_fin,
                date_exploit_debut   = :date_exploit_debut,
                date_exploit_fin     = :date_exploit_fin,
                date_demontage_debut = :date_demontage_debut,
                date_demontage_fin   = :date_demontage_fin,
                drive_url            = :drive_url,
                drive_doc_url        = :drive_doc_url,
                maps_url             = :maps_url
             WHERE id = :id'
        );

        return $stmt->execute([
            'nom'                  => $data['nom'],
            'sport'                => $data['sport']                ?? null,
            'date_debut'           => $data['date_debut'],
            'date_fin'             => $data['date_fin']             ?? null,
            'lieu'                 => $data['lieu']                 ?? null,
            'capacite'             => $data['capacite']             ?? null,
            'description'          => $data['description']          ?? null,
            'date_preprod_debut'   => $data['date_preprod_debut']   ?: null,
            'date_preprod_fin'     => $data['date_preprod_fin']     ?: null,
            'date_prod_debut'      => $data['date_prod_debut']      ?: null,
            'date_prod_fin'        => $data['date_prod_fin']        ?: null,
            'date_exploit_debut'   => $data['date_exploit_debut']   ?: null,
            'date_exploit_fin'     => $data['date_exploit_fin']     ?: null,
            'date_demontage_debut' => $data['date_demontage_debut'] ?: null,
            'date_demontage_fin'   => $data['date_demontage_fin']   ?: null,
            'drive_url'            => $data['drive_url']            ?: null,
            'drive_doc_url'        => $data['drive_doc_url']        ?: null,
            'maps_url'             => $data['maps_url']             ?: null,
            'id'                   => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM evenements WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
    public function duplicate(int $sourceId, string $nouveauNom): bool
    {
        $source = $this->findById($sourceId);
        if (!$source) return false;

        $stmt = $this->db->prepare(
            'INSERT INTO evenements
                (projet_id, nom, sport, date_debut, date_fin, lieu, capacite, description,
                 date_preprod_debut, date_preprod_fin, date_prod_debut, date_prod_fin,
                 date_exploit_debut, date_exploit_fin, date_demontage_debut, date_demontage_fin,
                 drive_url, drive_doc_url, maps_url, source_event_id)
             VALUES
                (:projet_id, :nom, :sport, :date_debut, :date_fin, :lieu, :capacite, :description,
                 :date_preprod_debut, :date_preprod_fin, :date_prod_debut, :date_prod_fin,
                 :date_exploit_debut, :date_exploit_fin, :date_demontage_debut, :date_demontage_fin,
                 :drive_url, :drive_doc_url, :maps_url, :source_event_id)'
        );

        return $stmt->execute([
            'projet_id'            => $source['projet_id']            ?? null,
            'nom'                  => $nouveauNom,
            'sport'                => $source['sport']                ?? null,
            'date_debut'           => $source['date_debut'],
            'date_fin'             => $source['date_fin']             ?? null,
            'lieu'                 => $source['lieu']                 ?? null,
            'capacite'             => $source['capacite']             ?? null,
            'description'          => $source['description']          ?? null,
            'date_preprod_debut'   => $source['date_preprod_debut']   ?? null,
            'date_preprod_fin'     => $source['date_preprod_fin']     ?? null,
            'date_prod_debut'      => $source['date_prod_debut']      ?? null,
            'date_prod_fin'        => $source['date_prod_fin']        ?? null,
            'date_exploit_debut'   => $source['date_exploit_debut']   ?? null,
            'date_exploit_fin'     => $source['date_exploit_fin']     ?? null,
            'date_demontage_debut' => $source['date_demontage_debut'] ?? null,
            'date_demontage_fin'   => $source['date_demontage_fin']   ?? null,
            'drive_url'            => $source['drive_url']            ?? null,
            'drive_doc_url'        => $source['drive_doc_url']        ?? null,
            'maps_url'             => $source['maps_url']             ?? null,
            'source_event_id'      => $sourceId,
        ]);
    }
}