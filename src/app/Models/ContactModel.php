<?php

/**
 * YES – Your Event Solution
 * @file ContactModel.php
 * @version 2.0  –  2026
 * AJOUTS : liaison contacts ↔ événements/projets + nouveaux champs
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

    // ── Contacts CRUD ─────────────────────────────────────────

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
                     tshirt, pointure, telephone_modele, poids, pieces_ok, type,
                     societe, poste, site_web, adresse, notes)
                 VALUES
                    (:nom, :infos, :telephone, :mail, :comm, :contact_urgence, :tel_urgence,
                     :tshirt, :pointure, :telephone_modele, :poids, :pieces_ok, :type,
                     :societe, :poste, :site_web, :adresse, :notes)'
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
                    poids=:poids, pieces_ok=:pieces_ok, type=:type,
                    societe=:societe, poste=:poste, site_web=:site_web,
                    adresse=:adresse, notes=:notes
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
            // Supprimer les liaisons d'abord
            $this->db->prepare('DELETE FROM contact_evenement WHERE contact_id = :id')->execute(['id' => $id]);
            $this->db->prepare('DELETE FROM contact_projet    WHERE contact_id = :id')->execute(['id' => $id]);
            return $this->db->prepare('DELETE FROM contacts WHERE id = :id')->execute(['id' => $id]);
        } catch (PDOException) {
            return false;
        }
    }

    // ── Liaisons Contact ↔ Événement ─────────────────────────

    public function getByEvent(int $eventId): array
    {
        try {
            $stmt = $this->db->prepare(
                'SELECT c.*, ce.role AS lien_role, ce.note AS lien_note, ce.id AS lien_id
                 FROM contacts c
                 JOIN contact_evenement ce ON ce.contact_id = c.id
                 WHERE ce.event_id = :id
                 ORDER BY c.nom ASC'
            );
            $stmt->execute(['id' => $eventId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException) {
            return [];
        }
    }

    public function attachToEvent(int $contactId, int $eventId, string $role = '', string $note = ''): bool
    {
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO contact_evenement (contact_id, event_id, role, note)
                 VALUES (:contact_id, :event_id, :role, :note)
                 ON DUPLICATE KEY UPDATE role=VALUES(role), note=VALUES(note)'
            );
            return $stmt->execute([
                'contact_id' => $contactId,
                'event_id'   => $eventId,
                'role'       => $role ?: null,
                'note'       => $note ?: null,
            ]);
        } catch (PDOException) {
            return false;
        }
    }

    public function detachFromEvent(int $lienId): bool
    {
        try {
            $stmt = $this->db->prepare('DELETE FROM contact_evenement WHERE id = :id');
            return $stmt->execute(['id' => $lienId]);
        } catch (PDOException) {
            return false;
        }
    }

    // ── Liaisons Contact ↔ Projet ────────────────────────────

    public function getByProjet(int $projetId): array
    {
        try {
            $stmt = $this->db->prepare(
                'SELECT c.*, cp.role AS lien_role, cp.note AS lien_note, cp.id AS lien_id
                 FROM contacts c
                 JOIN contact_projet cp ON cp.contact_id = c.id
                 WHERE cp.projet_id = :id
                 ORDER BY c.nom ASC'
            );
            $stmt->execute(['id' => $projetId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException) {
            return [];
        }
    }

    public function attachToProjet(int $contactId, int $projetId, string $role = '', string $note = ''): bool
    {
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO contact_projet (contact_id, projet_id, role, note)
                 VALUES (:contact_id, :projet_id, :role, :note)
                 ON DUPLICATE KEY UPDATE role=VALUES(role), note=VALUES(note)'
            );
            return $stmt->execute([
                'contact_id' => $contactId,
                'projet_id'  => $projetId,
                'role'       => $role ?: null,
                'note'       => $note ?: null,
            ]);
        } catch (PDOException) {
            return false;
        }
    }

    public function detachFromProjet(int $lienId): bool
    {
        try {
            $stmt = $this->db->prepare('DELETE FROM contact_projet WHERE id = :id');
            return $stmt->execute(['id' => $lienId]);
        } catch (PDOException) {
            return false;
        }
    }

    // ── Utilisateurs internes ────────────────────────────────

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
                'poids'            => !empty($d['poids']) ? (float)$d['poids'] : null,
                'pieces_ok'        => (int)($d['pieces_ok'] ?? 0),
                'id'               => $id,
            ]);
        } catch (PDOException) {
            return false;
        }
    }

    // ── Helpers ──────────────────────────────────────────────

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
            'poids'            => !empty($d['poids']) ? (float)$d['poids'] : null,
            'pieces_ok'        => (int)($d['pieces_ok']  ?? 0),
            'type'             => in_array($d['type'] ?? '', ['contact','staff'], true) ? $d['type'] : 'contact',
            'societe'          => $d['societe']          ?? null,
            'poste'            => $d['poste']            ?? null,
            'site_web'         => $d['site_web']         ?? null,
            'adresse'          => $d['adresse']          ?? null,
            'notes'            => $d['notes']            ?? null,
        ];
    }
}