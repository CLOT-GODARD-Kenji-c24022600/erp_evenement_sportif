<?php

/**
 * YES – Your Event Solution
 * @file ContactController.php
 * @version 2.0  –  2026
 * AJOUTS : attach/detach événement et projet
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Models\ContactModel;
use App\Models\EventModel;
use App\Models\ProjectModel;
use Core\Permission;
use Core\Security;

class ContactController
{
    private ContactModel $model;

    public function __construct()
    {
        $this->model = new ContactModel();
    }

    public function index(): array
    {
        $msg  = null;
        $type = 'success';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['contact_action'])) {
            if (Permission::canAnnuaire(Permission::currentRole())) {
                [$type, $msg] = explode(':', $this->handlePost(), 2);
            } else {
                [$type, $msg] = ['error', 'Accès refusé.'];
            }
        }

        // Événements et projets pour les modals de liaison
        $evenements = [];
        $projets    = [];
        try { $evenements = (new EventModel())->getAll(); }        catch (\Exception) {}
        try { $projets    = (new ProjectModel())->getAllSimple(); } catch (\Exception) {}

        return [
            'contacts'     => $this->model->getAll(),
            'usersInterne' => $this->model->getAllUsers(),
            'evenements'   => $evenements,
            'projets'      => $projets,
            'contactMsg'   => $msg,
            'contactType'  => $type,
        ];
    }

    private function handlePost(): string
    {
        $action = Security::sanitizeString($_POST['contact_action'] ?? '');

        return match ($action) {
            'create'           => $this->create(),
            'update'           => $this->update(),
            'delete'           => $this->delete(),
            'update_user'      => $this->updateUser(),
            'transfer_user'    => $this->transferUser(),
            'attach_event'     => $this->attachEvent(),
            'detach_event'     => $this->detachEvent(),
            'attach_projet'    => $this->attachProjet(),
            'detach_projet'    => $this->detachProjet(),
            default            => 'error:Action inconnue.',
        };
    }

    // ── CRUD Contact ─────────────────────────────────────────

    private function create(): string
    {
        $nom = Security::sanitizeString($_POST['nom'] ?? '');
        if ($nom === '') return 'error:Le nom est obligatoire.';
        $ok = $this->model->create($this->fields());
        return $ok ? 'success:Contact ajouté.' : 'error:Erreur lors de l\'ajout.';
    }

    private function update(): string
    {
        $id = Security::sanitizeInt($_POST['contact_id'] ?? 0);
        if (!$id) return 'error:ID invalide.';
        $ok = $this->model->update($id, $this->fields());
        return $ok ? 'success:Contact mis à jour.' : 'error:Erreur mise à jour.';
    }

    private function delete(): string
    {
        $id = Security::sanitizeInt($_POST['contact_id'] ?? 0);
        return $id && $this->model->delete($id)
            ? 'success:Contact supprimé.' : 'error:Erreur suppression.';
    }

    // ── Liaisons Contact ↔ Événement ─────────────────────────

    private function attachEvent(): string
    {
        $contactId = Security::sanitizeInt($_POST['contact_id'] ?? 0);
        $eventId   = Security::sanitizeInt($_POST['event_id']   ?? 0);
        if (!$contactId || !$eventId) return 'error:Données invalides.';
        $role = Security::sanitizeString($_POST['role'] ?? '');
        $note = Security::sanitizeString($_POST['note'] ?? '');
        return $this->model->attachToEvent($contactId, $eventId, $role, $note)
            ? 'success:Contact lié à l\'événement.'
            : 'error:Erreur lors de la liaison.';
    }

    private function detachEvent(): string
    {
        $lienId = Security::sanitizeInt($_POST['lien_id'] ?? 0);
        return $lienId && $this->model->detachFromEvent($lienId)
            ? 'success:Contact détaché de l\'événement.'
            : 'error:Erreur suppression.';
    }

    // ── Liaisons Contact ↔ Projet ────────────────────────────

    private function attachProjet(): string
    {
        $contactId = Security::sanitizeInt($_POST['contact_id'] ?? 0);
        $projetId  = Security::sanitizeInt($_POST['projet_id']  ?? 0);
        if (!$contactId || !$projetId) return 'error:Données invalides.';
        $role = Security::sanitizeString($_POST['role'] ?? '');
        $note = Security::sanitizeString($_POST['note'] ?? '');
        return $this->model->attachToProjet($contactId, $projetId, $role, $note)
            ? 'success:Contact lié au projet.'
            : 'error:Erreur lors de la liaison.';
    }

    private function detachProjet(): string
    {
        $lienId = Security::sanitizeInt($_POST['lien_id'] ?? 0);
        return $lienId && $this->model->detachFromProjet($lienId)
            ? 'success:Contact détaché du projet.'
            : 'error:Erreur suppression.';
    }

    // ── Utilisateurs internes ────────────────────────────────

    private function transferUser(): string
    {
        $userId = Security::sanitizeInt($_POST['transfer_user_id'] ?? 0);
        if (!$userId) return 'error:ID invalide.';
        $users = $this->model->getAllUsers();
        $u     = null;
        foreach ($users as $row) {
            if ((int)$row['id'] === $userId) { $u = $row; break; }
        }
        if (!$u) return 'error:Membre introuvable.';
        $ok = $this->model->create([
            'nom'              => trim(($u['prenom'] ?? '') . ' ' . ($u['nom'] ?? '')),
            'infos'            => $u['poste']            ?? null,
            'telephone'        => $u['telephone']        ?? null,
            'mail'             => $u['email']            ?? null,
            'comm'             => $u['comm']             ?? null,
            'contact_urgence'  => $u['contact_urgence']  ?? null,
            'tel_urgence'      => $u['tel_urgence']      ?? null,
            'tshirt'           => $u['tshirt']           ?? null,
            'pointure'         => $u['pointure']         ?? null,
            'telephone_modele' => $u['telephone_modele'] ?? null,
            'poids'            => $u['poids']            ?? null,
            'pieces_ok'        => $u['pieces_ok']        ?? 0,
            'type'             => in_array($_POST['type'] ?? '', ['contact','staff'], true)
                                  ? $_POST['type'] : 'staff',
        ]);
        return $ok
            ? 'success:Membre copié dans les contacts externes.'
            : 'error:Erreur lors de la copie.';
    }

    private function updateUser(): string
    {
        $id = Security::sanitizeInt($_POST['user_id'] ?? 0);
        if (!$id) return 'error:ID invalide.';
        $ok = $this->model->updateUser($id, [
            'nom'              => Security::sanitizeString($_POST['nom']    ?? ''),
            'prenom'           => Security::sanitizeString($_POST['prenom'] ?? ''),
            'email'            => Security::sanitizeString($_POST['email']  ?? ''),
            'poste'            => Security::sanitizeString($_POST['poste']  ?? ''),
            'telephone'        => Security::sanitizeString($_POST['telephone']        ?? ''),
            'infos'            => Security::sanitizeString($_POST['infos']            ?? ''),
            'comm'             => Security::sanitizeString($_POST['comm']             ?? ''),
            'contact_urgence'  => Security::sanitizeString($_POST['contact_urgence']  ?? ''),
            'tel_urgence'      => Security::sanitizeString($_POST['tel_urgence']      ?? ''),
            'tshirt'           => Security::sanitizeString($_POST['tshirt']           ?? ''),
            'pointure'         => Security::sanitizeString($_POST['pointure']         ?? ''),
            'telephone_modele' => Security::sanitizeString($_POST['telephone_modele'] ?? ''),
            'poids'            => $_POST['poids'] ?? null,
            'pieces_ok'        => isset($_POST['pieces_ok']) ? 1 : 0,
        ]);
        return $ok ? 'success:Membre mis à jour.' : 'error:Erreur mise à jour.';
    }

    private function fields(): array
    {
        return [
            'nom'              => Security::sanitizeString($_POST['nom']              ?? ''),
            'infos'            => Security::sanitizeString($_POST['infos']            ?? ''),
            'telephone'        => Security::sanitizeString($_POST['telephone']        ?? ''),
            'mail'             => Security::sanitizeString($_POST['mail']             ?? ''),
            'comm'             => Security::sanitizeString($_POST['comm']             ?? ''),
            'contact_urgence'  => Security::sanitizeString($_POST['contact_urgence']  ?? ''),
            'tel_urgence'      => Security::sanitizeString($_POST['tel_urgence']      ?? ''),
            'tshirt'           => Security::sanitizeString($_POST['tshirt']           ?? ''),
            'pointure'         => Security::sanitizeString($_POST['pointure']         ?? ''),
            'telephone_modele' => Security::sanitizeString($_POST['telephone_modele'] ?? ''),
            'poids'            => $_POST['poids'] ?? null,
            'pieces_ok'        => isset($_POST['pieces_ok']) ? 1 : 0,
            'type'             => in_array($_POST['type'] ?? '', ['contact','staff'], true)
                                  ? $_POST['type'] : 'contact',
            'societe'          => Security::sanitizeString($_POST['societe']  ?? ''),
            'poste'            => Security::sanitizeString($_POST['poste']    ?? ''),
            'site_web'         => Security::sanitizeString($_POST['site_web'] ?? ''),
            'adresse'          => Security::sanitizeString($_POST['adresse']  ?? ''),
            'notes'            => Security::sanitizeString($_POST['notes']    ?? ''),
        ];
    }
}