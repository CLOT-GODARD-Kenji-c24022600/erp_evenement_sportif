<?php

/**
 * YES – Your Event Solution
 * @file ContactModel.php
 * @version 1.0  –  2026
 */

declare(strict_types=1);

namespace App\Models;

use Core\Database;
use PDO;
use PDOException;

class ContactModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAll(): array
    {
        try {
            return $this->db->query(
                'SELECT * FROM contacts ORDER BY type ASC, nom ASC'
            )->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException) {
            return [];
        }
    }

    public function findById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare('SELECT * FROM contacts WHERE id = :id LIMIT 1');
            $stmt->execute(['id' => $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row !== false ? $row : null;
        } catch (PDOException) {
            return null;
        }
    }

    public function create(array $d): bool
    {
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO contacts
                    (nom, infos, telephone, mail, comm, contact_urgence, tel_urgence,
                     tshirt, pointure, telephone_modele, poids, pieces_ok, type)
                 VALUES
                    (:nom, :infos, :telephone, :mail, :comm, :contact_urgence, :tel_urgence,
                     :tshirt, :pointure, :telephone_modele, :poids, :pieces_ok, :type)'
            );
            return $stmt->execute($this->bind($d));
        } catch (PDOException) {
            return false;
        }
    }

    public function update(int $id, array $d): bool
    {
        try {
            $stmt = $this->db->prepare(
                'UPDATE contacts SET
                    nom=:nom, infos=:infos, telephone=:telephone, mail=:mail, comm=:comm,
                    contact_urgence=:contact_urgence, tel_urgence=:tel_urgence,
                    tshirt=:tshirt, pointure=:pointure, telephone_modele=:telephone_modele,
                    poids=:poids, pieces_ok=:pieces_ok, type=:type
                 WHERE id=:id'
            );
            return $stmt->execute(array_merge($this->bind($d), ['id' => $id]));
        } catch (PDOException) {
            return false;
        }
    }

    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare('DELETE FROM contacts WHERE id = :id');
            return $stmt->execute(['id' => $id]);
        } catch (PDOException) {
            return false;
        }
    }

    /** Également : utilisateurs internes enrichis */
    public function getAllUsers(): array
    {
        try {
            return $this->db->query(
                "SELECT id, nom, prenom, email, role, statut, poste,
                        telephone, infos, comm,
                        contact_urgence, tel_urgence,
                        tshirt, pointure, telephone_modele, poids, pieces_ok,
                        avatar, statut_presence
                 FROM utilisateurs
                 WHERE statut = 'approuve'
                 ORDER BY nom ASC, prenom ASC"
            )->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException) {
            return [];
        }
    }

    public function updateUser(int $id, array $d): bool
    {
        try {
            $stmt = $this->db->prepare(
                'UPDATE utilisateurs SET
                    nom=:nom, prenom=:prenom, email=:email, poste=:poste,
                    telephone=:telephone, infos=:infos, comm=:comm,
                    contact_urgence=:contact_urgence, tel_urgence=:tel_urgence,
                    tshirt=:tshirt, pointure=:pointure, telephone_modele=:telephone_modele,
                    poids=:poids, pieces_ok=:pieces_ok
                 WHERE id=:id'
            );
            return $stmt->execute([
                'nom'              => $d['nom']              ?? '',
                'prenom'           => $d['prenom']           ?? '',
                'email'            => $d['email']            ?? '',
                'poste'            => $d['poste']            ?? null,
                'telephone'        => $d['telephone']        ?? null,
                'infos'            => $d['infos']            ?? null,
                'comm'             => $d['comm']             ?? null,
                'contact_urgence'  => $d['contact_urgence']  ?? null,
                'tel_urgence'      => $d['tel_urgence']      ?? null,
                'tshirt'           => $d['tshirt']           ?? null,
                'pointure'         => $d['pointure']         ?? null,
                'telephone_modele' => $d['telephone_modele'] ?? null,
                'poids'            => !empty($d['poids']) ? (float) $d['poids'] : null,
                'pieces_ok'        => (int) ($d['pieces_ok'] ?? 0),
                'id'               => $id,
            ]);
        } catch (PDOException) {
            return false;
        }
    }

    private function bind(array $d): array
    {
        return [
            'nom'              => $d['nom']              ?? '',
            'infos'            => $d['infos']            ?? null,
            'telephone'        => $d['telephone']        ?? null,
            'mail'             => $d['mail']             ?? null,
            'comm'             => $d['comm']             ?? null,
            'contact_urgence'  => $d['contact_urgence']  ?? null,
            'tel_urgence'      => $d['tel_urgence']      ?? null,
            'tshirt'           => $d['tshirt']           ?? null,
            'pointure'         => $d['pointure']         ?? null,
            'telephone_modele' => $d['telephone_modele'] ?? null,
            'poids'            => !empty($d['poids']) ? (float) $d['poids'] : null,
            'pieces_ok'        => (int) ($d['pieces_ok'] ?? 0),
            'type'             => in_array($d['type'] ?? '', ['contact','staff'], true) ? $d['type'] : 'contact',
        ];
    }
}