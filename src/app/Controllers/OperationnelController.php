<?php

/**
 * YES – Your Event Solution
 * @file OperationnelController.php
 * @version 2.0  –  2026
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Models\BudgetModel;
use App\Models\PlanningModel;
use App\Models\MaterielModel;
use App\Models\FacturationModel;
use App\Models\EventModel;
use App\Models\ProjectModel;
use App\Models\ContactModel;
use Core\Security;


class OperationnelController
{
    private BudgetModel      $budget;
    private PlanningModel    $planning;
    private MaterielModel    $materiel;
    private FacturationModel $facturation;

    public function __construct()
    {
        $this->budget      = new BudgetModel();
        $this->planning    = new PlanningModel();
        $this->materiel    = new MaterielModel();
        $this->facturation = new FacturationModel();
    }

    public function index(): array
    {
        // ✅ Mémorisation de la dernière page visitée (event_id / projet_id)
        $eventId  = Security::sanitizeInt($_GET['event_id']  ?? $_SESSION['ops_last_event']  ?? 0);
        $projetId = Security::sanitizeInt($_GET['projet_id'] ?? $_SESSION['ops_last_projet'] ?? 0);

        // Lire le message flash depuis la session (après redirect POST→GET)
        $msg  = $_SESSION['ops_msg']  ?? null;
        $type = $_SESSION['ops_type'] ?? 'success';
        unset($_SESSION['ops_msg'], $_SESSION['ops_type']);

        // Si POST : handlePost fait la redirect lui-même (exit)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['ops_action'])) {
            $eventId  = Security::sanitizeInt($_POST['event_id']  ?? $eventId);
            $projetId = Security::sanitizeInt($_POST['projet_id'] ?? $projetId);
            $this->handlePost($eventId, $projetId);
        }

        // Mémoriser le contexte en session pour "rester sur la même page"
        if ($eventId > 0)  $_SESSION['ops_last_event']  = $eventId;
        if ($projetId > 0) $_SESSION['ops_last_projet'] = $projetId;

        $evenements = [];
        $projets    = [];
        try { $evenements = (new EventModel())->getAll(); }        catch (\Exception $e) {}
        try { $projets    = (new ProjectModel())->getAllSimple(); } catch (\Exception $e) {}

        // Contacts pour la facturation
        $contacts = [];
        try { $contacts = (new ContactModel())->getAll(); } catch (\Exception $e) {}

        $planning        = [];
        $materiel        = [];
        $facturation     = [];
        $budget          = [];
        $budgetTotaux    = [];
        $materielTotaux  = [];
        $eventData       = null;
        $projetFinance   = [];
        $contactsLies    = [];

        try {
            if ($eventId > 0) {
                $planning       = $this->planning->getByEvent($eventId);
                $materiel       = $this->materiel->getByEvent($eventId);
                $facturation    = $this->facturation->getByEvent($eventId);
                $budget         = $this->budget->getByEvent($eventId);
                $budgetTotaux   = $this->budget->getTotauxEvent($eventId);
                $materielTotaux = $this->materiel->getBudgetTotauxEvent($eventId);
                $eventData      = (new EventModel())->findById($eventId);
                $contactsLies   = (new ContactModel())->getByEvent($eventId);
            } elseif ($projetId > 0) {
                $planning       = $this->planning->getByProjet($projetId);
                $materiel       = $this->materiel->getByProjet($projetId);
                $facturation    = $this->facturation->getByProjet($projetId);
                $budget         = $this->budget->getByProjet($projetId);
                $budgetTotaux   = $this->budget->getTotauxProjet($projetId);
                $materielTotaux = $this->materiel->getBudgetTotauxProjet($projetId);
                $contactsLies   = (new ContactModel())->getByProjet($projetId);
                // Récap finance projet
                $projetData = (new ProjectModel())->findById($projetId);
                if ($projetData) {
                    $projetFinance = [
                        'budget'   => (float) ($projetData['budget']          ?? 0),
                        'recettes' => (float) ($projetData['total_recettes']  ?? 0),
                        'depenses' => (float) ($projetData['total_depenses']  ?? 0),
                        'solde'    => (float) ($projetData['total_recettes']  ?? 0) - (float) ($projetData['total_depenses'] ?? 0),
                    ];
                }
            }
        } catch (\Exception $e) {
            $msg  = 'Tables non encore créées — lance migration_v2.sql d\'abord.';
            $type = 'error';
        }

        // Lire et effacer l'onglet à restaurer
        $restoreTab = $_SESSION['ops_restore_tab'] ?? null;
        unset($_SESSION['ops_restore_tab']);

        return [
            'evenements'     => $evenements,
            'projets'        => $projets,
            'contacts'       => $contacts,
            'eventId'        => $eventId,
            'projetId'       => $projetId,
            'eventData'      => $eventData,
            'restoreTab'     => $restoreTab,
            'planning'       => $planning,
            'materiel'       => $materiel,
            'facturation'    => $facturation,
            'budget'         => $budget,
            'budgetTotaux'   => $budgetTotaux,
            'materielTotaux' => $materielTotaux,
            'projetFinance'  => $projetFinance,
            'contactsLies'   => $contactsLies,
            'opsMsg'         => $msg,
            'opsType'        => $type,
        ];
    }

    private function handlePost(int $eventId, int $projetId): void
    {
        $action    = Security::sanitizeString($_POST['ops_action'] ?? '');
        $activeTab = Security::sanitizeString($_POST['active_tab'] ?? '#pane-budget');

        $allowedTabs = [
            '#pane-budget', '#pane-planning', '#pane-materiel',
            '#pane-facturation', '#pane-preprod'
        ];
        if (!in_array($activeTab, $allowedTabs, true)) {
            $activeTab = '#pane-budget';
        }

        // Stocker l'onglet en session pour le récupérer après redirect SPA
        $_SESSION['ops_restore_tab'] = $activeTab;
        $tabParam = ltrim($activeTab, '#');

        // Gestion upload fichier facturation
        if (in_array($action, ['facturation_create', 'facturation_update'], true)) {
            $_POST['fichier'] = $this->handleFacturationUpload();
        }

        $result = match($action) {
            // Planning
            'planning_create' => $this->planningCreate($eventId, $projetId),
            'planning_update' => $this->planningUpdate(),
            'planning_delete' => $this->planningDelete(),
            'planning_statut' => $this->planningStatut(),
            // Matériel
            'materiel_create' => $this->materielCreate($eventId, $projetId),
            'materiel_update' => $this->materielUpdate(),
            'materiel_delete' => $this->materielDelete(),
            // Facturation
            'facturation_create' => $this->facturationCreate($eventId, $projetId),
            'facturation_update' => $this->facturationUpdate(),
            'facturation_delete' => $this->facturationDelete(),
            // Budget
            'budget_create' => $this->budgetCreate($eventId, $projetId),
            'budget_update' => $this->budgetUpdate(),
            'budget_delete' => $this->budgetDelete(),
            // Événement — liaisons Google Drive / Maps
            'event_drive_update' => $this->eventDriveUpdate($eventId),
            'contact_detach'     => $this->contactDetach(),
            default => 'error:Action inconnue.',
        };

        [$type, $msg] = explode(':', $result, 2);

        $_SESSION['ops_msg']  = $msg;
        $_SESSION['ops_type'] = $type;

        $base = '/operationnel?event_id=' . $eventId . '&projet_id=' . $projetId . '&tab=' . $tabParam;
        header('Location: ' . $base);
        exit;
    }

    // ── Gestion upload fichier facturation ───────────────────

    private function handleFacturationUpload(): ?string
    {
        if (empty($_FILES['fichier_upload']['name'])) {
            // Conserver l'ancien fichier si présent dans POST
            return Security::sanitizeString($_POST['fichier_existing'] ?? '') ?: null;
        }

        $uploadDir = __DIR__ . '/../../../public/uploads/facturation/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ext      = strtolower(pathinfo($_FILES['fichier_upload']['name'], PATHINFO_EXTENSION));
        $allowed  = ['pdf','jpg','jpeg','png','doc','docx','xls','xlsx'];
        if (!in_array($ext, $allowed, true)) return null;

        $filename = 'fac_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $dest     = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['fichier_upload']['tmp_name'], $dest)) {
            return 'uploads/facturation/' . $filename;
        }
        return null;
    }

    // ── Event Drive / Maps ────────────────────────────────────

    private function eventDriveUpdate(int $eventId): string
    {
        if ($eventId <= 0) return 'error:Événement non sélectionné.';
        $model = new EventModel();
        $event = $model->findById($eventId);
        if (!$event) return 'error:Événement introuvable.';

        $data = array_merge($event, [
            'drive_url'     => Security::sanitizeString($_POST['drive_url']     ?? ''),
            'drive_doc_url' => Security::sanitizeString($_POST['drive_doc_url'] ?? ''),
            'maps_url'      => Security::sanitizeString($_POST['maps_url']      ?? ''),
        ]);
        return $model->update($eventId, $data) ? 'success:Liens Drive/Maps mis à jour.' : 'error:Erreur lors de la mise à jour.';
    }

    // ── Planning ─────────────────────────────────────────────

    private function planningCreate(int $eventId, int $projetId): string
    {
        $tache = Security::sanitizeString($_POST['tache'] ?? '');
        if ($tache === '') return 'error:La tâche est obligatoire.';
        $ok = $this->planning->create([
            'event_id'   => $eventId  ?: null,
            'projet_id'  => $projetId ?: null,
            'tache'      => $tache,
            'statut'     => $_POST['statut']     ?? 'wip',
            'date_debut' => $_POST['date_debut'] ?: null,
            'date_fin'   => $_POST['date_fin']   ?: null,
            'note'       => Security::sanitizeString($_POST['note'] ?? ''),
            'ordre'      => (int) ($_POST['ordre'] ?? 0),
        ]);
        return $ok ? 'success:Tâche de planning ajoutée.' : 'error:Erreur lors de l\'ajout.';
    }

    private function planningUpdate(): string
    {
        $id = Security::sanitizeInt($_POST['ligne_id'] ?? 0);
        if (!$id) return 'error:ID invalide.';
        $ok = $this->planning->update($id, [
            'tache'      => Security::sanitizeString($_POST['tache'] ?? ''),
            'statut'     => $_POST['statut']     ?? 'wip',
            'date_debut' => $_POST['date_debut'] ?: null,
            'date_fin'   => $_POST['date_fin']   ?: null,
            'note'       => Security::sanitizeString($_POST['note'] ?? ''),
            'ordre'      => (int) ($_POST['ordre'] ?? 0),
        ]);
        return $ok ? 'success:Tâche mise à jour.' : 'error:Erreur lors de la mise à jour.';
    }

    private function planningDelete(): string
    {
        $id = Security::sanitizeInt($_POST['ligne_id'] ?? 0);
        return $id && $this->planning->delete($id)
            ? 'success:Ligne supprimée.' : 'error:Erreur suppression.';
    }

    private function planningStatut(): string
    {
        $id     = Security::sanitizeInt($_POST['ligne_id'] ?? 0);
        $statut = Security::sanitizeString($_POST['statut'] ?? '');
        return $id && $this->planning->updateStatut($id, $statut)
            ? 'success:Statut mis à jour.' : 'error:Statut invalide.';
    }

    // ── Matériel ─────────────────────────────────────────────

    private function materielCreate(int $eventId, int $projetId): string
    {
        $nom = Security::sanitizeString($_POST['nom'] ?? '');
        if ($nom === '') return 'error:Le nom est obligatoire.';
        $ok = $this->materiel->create([
            'event_id'        => $eventId  ?: null,
            'projet_id'       => $projetId ?: null,
            'nom'             => $nom,
            'quantite'        => $_POST['quantite']         ?? 1,
            'fournisseur'     => Security::sanitizeString($_POST['fournisseur']     ?? ''),
            'date_in'         => $_POST['date_in']          ?: null,
            'date_out'        => $_POST['date_out']         ?: null,
            'commentaire'     => Security::sanitizeString($_POST['commentaire']     ?? ''),
            'categorie_achat' => Security::sanitizeString($_POST['categorie_achat'] ?? ''),
            'budget'          => $_POST['budget'] !== '' ? $_POST['budget'] : null,
        ]);
        return $ok ? 'success:Matériel ajouté.' : 'error:Erreur lors de l\'ajout.';
    }

    private function materielUpdate(): string
    {
        $id = Security::sanitizeInt($_POST['ligne_id'] ?? 0);
        if (!$id) return 'error:ID invalide.';
        $ok = $this->materiel->update($id, [
            'nom'             => Security::sanitizeString($_POST['nom']             ?? ''),
            'quantite'        => $_POST['quantite']         ?? 1,
            'fournisseur'     => Security::sanitizeString($_POST['fournisseur']     ?? ''),
            'date_in'         => $_POST['date_in']          ?: null,
            'date_out'        => $_POST['date_out']         ?: null,
            'commentaire'     => Security::sanitizeString($_POST['commentaire']     ?? ''),
            'categorie_achat' => Security::sanitizeString($_POST['categorie_achat'] ?? ''),
            'budget'          => $_POST['budget'] !== '' ? $_POST['budget'] : null,
        ]);
        return $ok ? 'success:Matériel mis à jour.' : 'error:Erreur mise à jour.';
    }

    private function materielDelete(): string
    {
        $id = Security::sanitizeInt($_POST['ligne_id'] ?? 0);
        return $id && $this->materiel->delete($id)
            ? 'success:Matériel supprimé.' : 'error:Erreur suppression.';
    }

    // ── Facturation ──────────────────────────────────────────

    private function facturationCreate(int $eventId, int $projetId): string
    {
        // Si un contact existant est sélectionné, remplir auto les champs
        $contactId = Security::sanitizeInt($_POST['contact_id'] ?? 0);
        $contact   = Security::sanitizeString($_POST['contact']     ?? '');
        $tel       = Security::sanitizeString($_POST['telephone']   ?? '');
        $mail      = Security::sanitizeString($_POST['mail']        ?? '');

        if ($contactId > 0) {
            try {
                $c = (new ContactModel())->findById($contactId);
                if ($c) {
                    $contact = $c['nom']       ?? $contact;
                    $tel     = $c['telephone'] ?? $tel;
                    $mail    = $c['mail']      ?? $mail;
                }
            } catch (\Exception) {}
        }

        $ok = $this->facturation->create([
            'event_id'        => $eventId  ?: null,
            'projet_id'       => $projetId ?: null,
            'categorie'       => Security::sanitizeString($_POST['categorie']   ?? ''),
            'poste'           => Security::sanitizeString($_POST['poste']       ?? ''),
            'prestataire'     => Security::sanitizeString($_POST['prestataire'] ?? ''),
            'contact_id'      => $contactId ?: null,
            'contact'         => $contact,
            'telephone'       => $tel,
            'mail'            => $mail,
            'prix_unitaire'   => $_POST['prix_unitaire']   ?? 0,
            'quantite'        => $_POST['quantite']        ?? 1,
            'statut_devis'    => isset($_POST['statut_devis'])    ? 1 : 0,
            'statut_facture'  => isset($_POST['statut_facture'])  ? 1 : 0,
            'statut_virement' => isset($_POST['statut_virement']) ? 1 : 0,
            'note'            => Security::sanitizeString($_POST['note'] ?? ''),
            'fichier'         => $_POST['fichier'] ?? null,
        ]);
        return $ok ? 'success:Ligne de facturation ajoutée.' : 'error:Erreur lors de l\'ajout.';
    }

    private function facturationUpdate(): string
    {
        $id = Security::sanitizeInt($_POST['ligne_id'] ?? 0);
        if (!$id) return 'error:ID invalide.';

        $contactId = Security::sanitizeInt($_POST['contact_id'] ?? 0);
        $contact   = Security::sanitizeString($_POST['contact']     ?? '');
        $tel       = Security::sanitizeString($_POST['telephone']   ?? '');
        $mail      = Security::sanitizeString($_POST['mail']        ?? '');

        if ($contactId > 0) {
            try {
                $c = (new ContactModel())->findById($contactId);
                if ($c) {
                    $contact = $c['nom']       ?? $contact;
                    $tel     = $c['telephone'] ?? $tel;
                    $mail    = $c['mail']      ?? $mail;
                }
            } catch (\Exception) {}
        }

        // Conserver fichier existant si pas de nouvel upload
        $fichier = $_POST['fichier'] ?? null;

        $ok = $this->facturation->update($id, [
            'categorie'       => Security::sanitizeString($_POST['categorie']   ?? ''),
            'poste'           => Security::sanitizeString($_POST['poste']       ?? ''),
            'prestataire'     => Security::sanitizeString($_POST['prestataire'] ?? ''),
            'contact_id'      => $contactId ?: null,
            'contact'         => $contact,
            'telephone'       => $tel,
            'mail'            => $mail,
            'prix_unitaire'   => $_POST['prix_unitaire']   ?? 0,
            'quantite'        => $_POST['quantite']        ?? 1,
            'statut_devis'    => isset($_POST['statut_devis'])    ? 1 : 0,
            'statut_facture'  => isset($_POST['statut_facture'])  ? 1 : 0,
            'statut_virement' => isset($_POST['statut_virement']) ? 1 : 0,
            'note'            => Security::sanitizeString($_POST['note'] ?? ''),
            'fichier'         => $fichier,
        ]);
        return $ok ? 'success:Facturation mise à jour.' : 'error:Erreur mise à jour.';
    }

    private function facturationDelete(): string
    {
        $id = Security::sanitizeInt($_POST['ligne_id'] ?? 0);
        return $id && $this->facturation->delete($id)
            ? 'success:Ligne supprimée.' : 'error:Erreur suppression.';
    }

    // ── Budget ───────────────────────────────────────────────

    private function budgetCreate(int $eventId, int $projetId): string
    {
        $libelle = Security::sanitizeString($_POST['libelle'] ?? '');
        if ($libelle === '') return 'error:Le libellé est obligatoire.';
        $ok = $this->budget->create([
            'event_id'       => $eventId  ?: null,
            'projet_id'      => $projetId ?: null,
            'type'           => $_POST['type']            ?? 'charge',
            'categorie'      => Security::sanitizeString($_POST['categorie']      ?? ''),
            'sous_categorie' => Security::sanitizeString($_POST['sous_categorie'] ?? ''),
            'libelle'        => $libelle,
            'previsionnel'   => $_POST['previsionnel']   ?? 0,
            'comparatif'     => $_POST['comparatif']     ?? 0,
            'note'           => Security::sanitizeString($_POST['note']           ?? ''),
            'fournisseur'    => Security::sanitizeString($_POST['fournisseur']    ?? ''),
            'sponsor'        => Security::sanitizeString($_POST['sponsor']        ?? ''),
        ]);
        return $ok ? 'success:Ligne de budget ajoutée.' : 'error:Erreur lors de l\'ajout.';
    }

    private function budgetUpdate(): string
    {
        $id = Security::sanitizeInt($_POST['ligne_id'] ?? 0);
        if (!$id) return 'error:ID invalide.';
        $ok = $this->budget->update($id, [
            'type'           => $_POST['type']            ?? 'charge',
            'categorie'      => Security::sanitizeString($_POST['categorie']      ?? ''),
            'sous_categorie' => Security::sanitizeString($_POST['sous_categorie'] ?? ''),
            'libelle'        => Security::sanitizeString($_POST['libelle']        ?? ''),
            'previsionnel'   => $_POST['previsionnel']   ?? 0,
            'comparatif'     => $_POST['comparatif']     ?? 0,
            'note'           => Security::sanitizeString($_POST['note']           ?? ''),
            'fournisseur'    => Security::sanitizeString($_POST['fournisseur']    ?? ''),
            'sponsor'        => Security::sanitizeString($_POST['sponsor']        ?? ''),
        ]);
        return $ok ? 'success:Budget mis à jour.' : 'error:Erreur mise à jour.';
    }

    private function budgetDelete(): string
    {
        $id = Security::sanitizeInt($_POST['ligne_id'] ?? 0);
        return $id && $this->budget->delete($id)
            ? 'success:Ligne supprimée.' : 'error:Erreur suppression.';
    }
    // ── Contact détachement ──────────────────────────────

    private function contactDetach(): string
    {
        $lienId   = \Core\Security::sanitizeInt($_POST['lien_id']   ?? 0);
        $lienType = \Core\Security::sanitizeString($_POST['lien_type'] ?? '');
        if (!$lienId) return 'error:ID invalide.';

        $model = new ContactModel();
        $ok = $lienType === 'event'
            ? $model->detachFromEvent($lienId)
            : $model->detachFromProjet($lienId);

        return $ok ? 'success:Contact détaché.' : 'error:Erreur lors du détachement.';
    }
}