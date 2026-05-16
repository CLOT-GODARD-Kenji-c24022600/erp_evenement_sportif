<?php

/**
 * YES – Your Event Solution
 * @file OperationnelController.php
 * @version 1.1  –  2026
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Models\BudgetModel;
use App\Models\PlanningModel;
use App\Models\MaterielModel;
use App\Models\FacturationModel;
use App\Models\EventModel;
use App\Models\ProjectModel;
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
        // ✅ Lire le message flash depuis la session (après redirect POST→GET)
        $msg  = $_SESSION['ops_msg']  ?? null;
        $type = $_SESSION['ops_type'] ?? 'success';
        unset($_SESSION['ops_msg'], $_SESSION['ops_type']);

        $eventId  = Security::sanitizeInt($_GET['event_id']  ?? 0);
        $projetId = Security::sanitizeInt($_GET['projet_id'] ?? 0);

        // ✅ Si POST : handlePost fait la redirect lui-même (exit)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['ops_action'])) {
            $eventId  = Security::sanitizeInt($_POST['event_id']  ?? $eventId);
            $projetId = Security::sanitizeInt($_POST['projet_id'] ?? $projetId);
            $this->handlePost($eventId, $projetId);
        }

        $evenements = [];
        $projets    = [];
        try { $evenements = (new EventModel())->getAll(); }       catch (\Exception $e) {}
        try { $projets    = (new ProjectModel())->getAllSimple(); } catch (\Exception $e) {}

        $planning     = [];
        $materiel     = [];
        $facturation  = [];
        $budget       = [];
        $budgetTotaux = [];

        try {
            if ($eventId > 0) {
                $planning     = $this->planning->getByEvent($eventId);
                $materiel     = $this->materiel->getByEvent($eventId);
                $facturation  = $this->facturation->getByEvent($eventId);
                $budget       = $this->budget->getByEvent($eventId);
                $budgetTotaux = $this->budget->getTotauxEvent($eventId);
            } elseif ($projetId > 0) {
                $planning     = $this->planning->getByProjet($projetId);
                $materiel     = $this->materiel->getByProjet($projetId);
                $facturation  = $this->facturation->getByProjet($projetId);
                $budget       = $this->budget->getByProjet($projetId);
                $budgetTotaux = $this->budget->getTotauxProjet($projetId);
            }
        } catch (\Exception $e) {
            $msg  = 'Tables non encore créées — lance migration.sql d\'abord.';
            $type = 'error';
        }

        return [
            'evenements'   => $evenements,
            'projets'      => $projets,
            'eventId'      => $eventId,
            'projetId'     => $projetId,
            'planning'     => $planning,
            'materiel'     => $materiel,
            'facturation'  => $facturation,
            'budget'       => $budget,
            'budgetTotaux' => $budgetTotaux,
            'opsMsg'       => $msg,
            'opsType'      => $type,
        ];
    }

    private function handlePost(int $eventId, int $projetId): void
    {
        $action    = Security::sanitizeString($_POST['ops_action'] ?? '');
        $activeTab = Security::sanitizeString($_POST['active_tab'] ?? '#pane-budget');

        // Valider que le tab est bien un de ceux autorisés
        $allowedTabs = ['#pane-budget', '#pane-planning', '#pane-materiel', '#pane-facturation'];
        if (!in_array($activeTab, $allowedTabs, true)) {
            $activeTab = '#pane-budget';
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
            default         => 'error:Action inconnue.',
        };

        [$type, $msg] = explode(':', $result, 2);

        // ✅ Stocker le message en session pour l'afficher après redirect
        $_SESSION['ops_msg']  = $msg;
        $_SESSION['ops_type'] = $type;

        // ✅ Redirect POST → GET avec le bon onglet dans le hash
        $base = '/operationnel?event_id=' . $eventId . '&projet_id=' . $projetId;
        header('Location: ' . $base . $activeTab);
        exit;
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
            'event_id'    => $eventId  ?: null,
            'projet_id'   => $projetId ?: null,
            'nom'         => $nom,
            'quantite'    => $_POST['quantite']    ?? 1,
            'fournisseur' => Security::sanitizeString($_POST['fournisseur'] ?? ''),
            'date_in'     => $_POST['date_in']     ?: null,
            'date_out'    => $_POST['date_out']    ?: null,
            'commentaire' => Security::sanitizeString($_POST['commentaire'] ?? ''),
        ]);
        return $ok ? 'success:Matériel ajouté.' : 'error:Erreur lors de l\'ajout.';
    }

    private function materielUpdate(): string
    {
        $id = Security::sanitizeInt($_POST['ligne_id'] ?? 0);
        if (!$id) return 'error:ID invalide.';
        $ok = $this->materiel->update($id, [
            'nom'         => Security::sanitizeString($_POST['nom'] ?? ''),
            'quantite'    => $_POST['quantite']    ?? 1,
            'fournisseur' => Security::sanitizeString($_POST['fournisseur'] ?? ''),
            'date_in'     => $_POST['date_in']     ?: null,
            'date_out'    => $_POST['date_out']    ?: null,
            'commentaire' => Security::sanitizeString($_POST['commentaire'] ?? ''),
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
        $ok = $this->facturation->create([
            'event_id'        => $eventId  ?: null,
            'projet_id'       => $projetId ?: null,
            'categorie'       => Security::sanitizeString($_POST['categorie']   ?? ''),
            'poste'           => Security::sanitizeString($_POST['poste']       ?? ''),
            'prestataire'     => Security::sanitizeString($_POST['prestataire'] ?? ''),
            'contact'         => Security::sanitizeString($_POST['contact']     ?? ''),
            'telephone'       => Security::sanitizeString($_POST['telephone']   ?? ''),
            'mail'            => Security::sanitizeString($_POST['mail']        ?? ''),
            'prix_unitaire'   => $_POST['prix_unitaire']   ?? 0,
            'quantite'        => $_POST['quantite']        ?? 1,
            'statut_devis'    => isset($_POST['statut_devis'])    ? 1 : 0,
            'statut_facture'  => isset($_POST['statut_facture'])  ? 1 : 0,
            'statut_virement' => isset($_POST['statut_virement']) ? 1 : 0,
            'note'            => Security::sanitizeString($_POST['note'] ?? ''),
        ]);
        return $ok ? 'success:Ligne de facturation ajoutée.' : 'error:Erreur lors de l\'ajout.';
    }

    private function facturationUpdate(): string
    {
        $id = Security::sanitizeInt($_POST['ligne_id'] ?? 0);
        if (!$id) return 'error:ID invalide.';
        $ok = $this->facturation->update($id, [
            'categorie'       => Security::sanitizeString($_POST['categorie']   ?? ''),
            'poste'           => Security::sanitizeString($_POST['poste']       ?? ''),
            'prestataire'     => Security::sanitizeString($_POST['prestataire'] ?? ''),
            'contact'         => Security::sanitizeString($_POST['contact']     ?? ''),
            'telephone'       => Security::sanitizeString($_POST['telephone']   ?? ''),
            'mail'            => Security::sanitizeString($_POST['mail']        ?? ''),
            'prix_unitaire'   => $_POST['prix_unitaire']   ?? 0,
            'quantite'        => $_POST['quantite']        ?? 1,
            'statut_devis'    => isset($_POST['statut_devis'])    ? 1 : 0,
            'statut_facture'  => isset($_POST['statut_facture'])  ? 1 : 0,
            'statut_virement' => isset($_POST['statut_virement']) ? 1 : 0,
            'note'            => Security::sanitizeString($_POST['note'] ?? ''),
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
            'categorie'      => Security::sanitizeString($_POST['categorie']       ?? ''),
            'sous_categorie' => Security::sanitizeString($_POST['sous_categorie']  ?? ''),
            'libelle'        => $libelle,
            'previsionnel'   => $_POST['previsionnel']   ?? 0,
            'comparatif'     => $_POST['comparatif']     ?? 0,
            'note'           => Security::sanitizeString($_POST['note'] ?? ''),
        ]);
        return $ok ? 'success:Ligne de budget ajoutée.' : 'error:Erreur lors de l\'ajout.';
    }

    private function budgetUpdate(): string
    {
        $id = Security::sanitizeInt($_POST['ligne_id'] ?? 0);
        if (!$id) return 'error:ID invalide.';
        $ok = $this->budget->update($id, [
            'type'           => $_POST['type']            ?? 'charge',
            'categorie'      => Security::sanitizeString($_POST['categorie']       ?? ''),
            'sous_categorie' => Security::sanitizeString($_POST['sous_categorie']  ?? ''),
            'libelle'        => Security::sanitizeString($_POST['libelle']         ?? ''),
            'previsionnel'   => $_POST['previsionnel']   ?? 0,
            'comparatif'     => $_POST['comparatif']     ?? 0,
            'note'           => Security::sanitizeString($_POST['note'] ?? ''),
        ]);
        return $ok ? 'success:Budget mis à jour.' : 'error:Erreur mise à jour.';
    }

    private function budgetDelete(): string
    {
        $id = Security::sanitizeInt($_POST['ligne_id'] ?? 0);
        return $id && $this->budget->delete($id)
            ? 'success:Ligne supprimée.' : 'error:Erreur suppression.';
    }
}