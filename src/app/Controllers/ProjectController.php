<?php

/**
 * YES - Your Event Solution
 * 
 * @file ProjectController.php
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 2.1
 * @since 2026
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Models\ProjectModel;
use App\Models\EventModel;
use App\Models\HistoriqueModel;
use Core\Security;

class ProjectController
{
    private ProjectModel $model;

    public function __construct(ProjectModel $model)
    {
        $this->model = $model;
    }

    public function index(): array
    {
        $msg  = null;
        $type = 'success';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['project_action'])) {
            $result = $this->handlePost();
            if ($result !== null) {
                [$type, $msg] = explode(':', $result, 2);
            }
        }

        return [
            'projets'    => $this->model->getAll(),
            'projetMsg'  => $msg,
            'projetType' => $type,
        ];
    }

    public function detail(int $id): ?array
    {
        $msg  = null;
        $type = 'success';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['project_action'])) {
            $result = $this->handlePost();
            if ($result !== null) {
                [$type, $msg] = explode(':', $result, 2);
            }
        }

        $projet = $this->model->findById($id);
        if ($projet === null) return null;

        $allEvents      = (new EventModel())->getAll();
        $linkedIds      = array_column($this->model->getEvents($id), 'id');
        $unlinkedEvents = array_filter($allEvents, fn($e) => !in_array((int)$e['id'], $linkedIds, true));

        $evenements  = $this->model->getEvents($id);
        $ganttEvents = array_filter($evenements, fn($e) => !empty($e['date_debut']));

        return [
            'projet'         => $projet,
            'finance'        => $this->model->getFinance($id),
            'evenements'     => $evenements,
            'ganttEvents'    => array_values($ganttEvents),
            'unlinkedEvents' => array_values($unlinkedEvents),
            'projetMsg'      => $msg,
            'projetType'     => $type,
        ];
    }

    public function handlePost(): ?string
    {
        $action = Security::sanitizeString($_POST['project_action'] ?? '');

        return match ($action) {
            'create'         => $this->create(),
            'edit'           => $this->edit(),
            'delete'         => $this->delete(),
            'add_finance'    => $this->addFinance(),
            'delete_finance' => $this->deleteFinance(),
            'attach_event'   => $this->attachEvent(),
            'detach_event'   => $this->detachEvent(),
            'detach_contact' => $this->detachContact(),
            default          => null,
        };
    }

    private function create(): string
    {
        $nom = Security::sanitizeString($_POST['nom'] ?? '');
        if ($nom === '') return 'error:Le nom du projet est obligatoire.';

        $data = [
            'nom'         => $nom,
            'description' => Security::sanitizeString($_POST['description'] ?? ''),
            'statut'      => $_POST['statut']     ?? 'en_cours',
            'budget'      => $_POST['budget']     ?? null,
            'date_debut'  => $_POST['date_debut'] ?? null,
            'date_fin'    => $_POST['date_fin']   ?? null,
        ];

        $ok = $this->model->create($data);

        if ($ok) {
            try {
                $last = (int) \Core\Database::getConnection()->lastInsertId();
                HistoriqueModel::log('create', 'projet', $last, $nom, [
                    'nom'    => $nom,
                    'statut' => $data['statut'],
                ]);
            } catch (\Throwable) {}
        }

        return $ok ? 'success:Projet créé avec succès !' : 'error:Erreur lors de la création.';
    }

    private function edit(): string
    {
        $id  = Security::sanitizeInt($_POST['projet_id'] ?? 0);
        $nom = Security::sanitizeString($_POST['nom'] ?? '');
        if (!$id || $nom === '') return 'error:Données invalides.';

        $before = $this->model->findById($id);

        $data = [
            'nom'         => $nom,
            'description' => Security::sanitizeString($_POST['description'] ?? ''),
            'statut'      => $_POST['statut']     ?? 'en_cours',
            'budget'      => $_POST['budget']     ?? null,
            'date_debut'  => $_POST['date_debut'] ?? null,
            'date_fin'    => $_POST['date_fin']   ?? null,
        ];

        $ok = $this->model->update($id, $data);

        if ($ok) {
            HistoriqueModel::log('update', 'projet', $id, $nom, [
                'before' => array_intersect_key($before ?? [], array_flip(['nom', 'statut', 'budget', 'date_debut', 'date_fin'])),
                'after'  => array_intersect_key($data,         array_flip(['nom', 'statut', 'budget', 'date_debut', 'date_fin'])),
            ]);
        }

        return $ok ? 'success:Projet modifié.' : 'error:Erreur lors de la modification.';
    }

    private function delete(): string
    {
        $id = Security::sanitizeInt($_POST['projet_id'] ?? 0);
        if (!$id) return 'error:Identifiant invalide.';

        $projet = $this->model->findById($id);
        $nom    = $projet['nom'] ?? "#{$id}";

        $ok = $this->model->delete($id);

        if ($ok) {
            HistoriqueModel::log('delete', 'projet', $id, $nom, ['nom' => $nom]);
        }

        return $ok ? 'success:Projet supprimé.' : 'error:Erreur lors de la suppression.';
    }

    private function addFinance(): string
    {
        $projetId = Security::sanitizeInt($_POST['projet_id'] ?? 0);
        $libelle  = Security::sanitizeString($_POST['libelle'] ?? '');
        $montant  = (float) ($_POST['montant'] ?? 0);
        $type     = in_array($_POST['type'] ?? '', ['recette', 'depense'], true) ? $_POST['type'] : null;

        if (!$projetId || $libelle === '' || $montant <= 0 || $type === null) {
            return 'error:Données financières invalides.';
        }

        $lineData = [
            'projet_id'      => $projetId,
            'type'           => $type,
            'categorie'      => Security::sanitizeString($_POST['categorie'] ?? ''),
            'libelle'        => $libelle,
            'montant'        => $montant,
            'date_operation' => !empty($_POST['date_operation']) ? $_POST['date_operation'] : date('Y-m-d'),
            'note'           => Security::sanitizeString($_POST['note'] ?? ''),
        ];

        $ok = $this->model->addFinanceLine($lineData);

        if ($ok) {
            HistoriqueModel::log('create', 'budget', $projetId, $libelle, [
                'type'    => $type,
                'montant' => $montant,
                'libelle' => $libelle,
            ]);
        }

        return $ok ? 'success:Ligne financière ajoutée.' : 'error:Erreur lors de l\'ajout.';
    }

    private function deleteFinance(): string
    {
        $id       = Security::sanitizeInt($_POST['finance_id'] ?? 0);
        $projetId = Security::sanitizeInt($_POST['projet_id']  ?? 0);
        if (!$id || !$projetId) return 'error:Identifiant invalide.';

        $ok = $this->model->deleteFinanceLine($id, $projetId);

        if ($ok) {
            HistoriqueModel::log('delete', 'budget', $id, "Ligne #{$id} (projet #{$projetId})", [
                'finance_id' => $id,
                'projet_id'  => $projetId,
            ]);
        }

        return $ok ? 'success:Ligne supprimée.' : 'error:Erreur lors de la suppression.';
    }

    private function attachEvent(): string
    {
        $projetId = Security::sanitizeInt($_POST['projet_id'] ?? 0);
        $eventId  = Security::sanitizeInt($_POST['event_id']  ?? 0);
        if (!$projetId || !$eventId) return 'error:Données invalides.';
        return $this->model->attachEvent($projetId, $eventId)
            ? 'success:Événement lié au projet.'
            : 'error:Erreur lors de la liaison.';
    }

    private function detachEvent(): string
    {
        $eventId = Security::sanitizeInt($_POST['event_id'] ?? 0);
        if (!$eventId) return 'error:Données invalides.';
        return $this->model->detachEvent($eventId)
            ? 'success:Événement détaché du projet.'
            : 'error:Erreur lors du détachement.';
    }

    private function detachContact(): string
    {
        $lienId = \Core\Security::sanitizeInt($_POST['lien_id'] ?? 0);
        if (!$lienId) return 'error:ID invalide.';
        $model = new \App\Models\ContactModel();
        return $model->detachFromProjet($lienId)
            ? 'success:Contact détaché du projet.'
            : 'error:Erreur lors du détachement.';
    }
}