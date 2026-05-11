<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\ProjectModel;
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
        if ($projet === null) {
            return null;
        }

        return [
            'projet'     => $projet,
            'finance'    => $this->model->getFinance($id),
            'evenements' => $this->model->getEvents($id),
            'projetMsg'  => $msg,
            'projetType' => $type,
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
            default          => null,
        };
    }

    private function create(): string
    {
        $nom = Security::sanitizeString($_POST['nom'] ?? '');
        if ($nom === '') {
            return 'error:Le nom du projet est obligatoire.';
        }
        $ok = $this->model->create([
            'nom'         => $nom,
            'description' => Security::sanitizeString($_POST['description'] ?? ''),
            'statut'      => $_POST['statut']     ?? 'en_cours',
            'budget'      => $_POST['budget']     ?? null,
            'date_debut'  => $_POST['date_debut'] ?? null,
            'date_fin'    => $_POST['date_fin']   ?? null,
        ]);
        return $ok ? 'success:Projet créé avec succès !' : 'error:Erreur lors de la création.';
    }

    private function edit(): string
    {
        $id  = Security::sanitizeInt($_POST['projet_id'] ?? 0);
        $nom = Security::sanitizeString($_POST['nom'] ?? '');
        if (!$id || $nom === '') {
            return 'error:Données invalides.';
        }
        $ok = $this->model->update($id, [
            'nom'         => $nom,
            'description' => Security::sanitizeString($_POST['description'] ?? ''),
            'statut'      => $_POST['statut']     ?? 'en_cours',
            'budget'      => $_POST['budget']     ?? null,
            'date_debut'  => $_POST['date_debut'] ?? null,
            'date_fin'    => $_POST['date_fin']   ?? null,
        ]);
        return $ok ? 'success:Projet modifié.' : 'error:Erreur lors de la modification.';
    }

    private function delete(): string
    {
        $id = Security::sanitizeInt($_POST['projet_id'] ?? 0);
        if (!$id) {
            return 'error:Identifiant invalide.';
        }
        return $this->model->delete($id) ? 'success:Projet supprimé.' : 'error:Erreur lors de la suppression.';
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
        $ok = $this->model->addFinanceLine([
            'projet_id'      => $projetId,
            'type'           => $type,
            'categorie'      => Security::sanitizeString($_POST['categorie'] ?? ''),
            'libelle'        => $libelle,
            'montant'        => $montant,
            'date_operation' => !empty($_POST['date_operation']) ? $_POST['date_operation'] : date('Y-m-d'),
            'note'           => Security::sanitizeString($_POST['note'] ?? ''),
        ]);
        return $ok ? 'success:Ligne financière ajoutée.' : 'error:Erreur lors de l\'ajout.';
    }

    private function deleteFinance(): string
    {
        $id       = Security::sanitizeInt($_POST['finance_id'] ?? 0);
        $projetId = Security::sanitizeInt($_POST['projet_id']  ?? 0);
        if (!$id || !$projetId) {
            return 'error:Identifiant invalide.';
        }
        return $this->model->deleteFinanceLine($id, $projetId)
            ? 'success:Ligne supprimée.'
            : 'error:Erreur lors de la suppression.';
    }
}