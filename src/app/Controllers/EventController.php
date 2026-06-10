<?php

/**
 * YES - Your Event Solution
 * 
 * @file EventController.php
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 2.1
 * @since 2026
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Models\EventModel;
use App\Models\HistoriqueModel;
use App\Models\PlanningModel;
use Core\Router;
use Core\Security;
use Core\Session;

class EventController
{
    private EventModel $eventModel;

    public function __construct(EventModel $eventModel)
    {
        $this->eventModel = $eventModel;
    }

    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect('/dashboard');
        }

        $data    = $this->collectPostData();
        $erreurs = $this->validateEvent($data['nom'], $data['date_debut'], $data['date_fin']);

        if (!empty($erreurs)) {
            Session::set('error_msg', implode('<br>', $erreurs));
            Router::redirect('/nouvel_event');
        }

        try {
            $this->eventModel->create($data);

            try {
                $last = $this->eventModel->getLastInsertedId();
                HistoriqueModel::log('create', 'evenement', $last, $data['nom'], [
                    'nom'        => $data['nom'],
                    'date_debut' => $data['date_debut'],
                    'lieu'       => $data['lieu'] ?? '',
                ]);

                // Synchroniser les phases de préproduction vers le planning si dates définies
                $planningModel = new PlanningModel();
                $planningModel->syncPhasesToPlanning($last, [
                    'preprod'   => ['debut' => $data['date_preprod_debut'],   'fin' => $data['date_preprod_fin'],   'label' => 'Pré-production'],
                    'prod'      => ['debut' => $data['date_prod_debut'],      'fin' => $data['date_prod_fin'],      'label' => 'Production / Installation'],
                    'exploit'   => ['debut' => $data['date_exploit_debut'],   'fin' => $data['date_exploit_fin'],   'label' => 'Exploitation / Événement'],
                    'demontage' => ['debut' => $data['date_demontage_debut'], 'fin' => $data['date_demontage_fin'], 'label' => 'Démontage'],
                ]);
            } catch (\Throwable) {}

            Session::set('success_msg', "L'événement '{$data['nom']}' a été enregistré avec succès !");
            Router::redirect('/dashboard');
        } catch (\Exception $e) {
            Session::set('error_msg', 'Erreur BDD : ' . $e->getMessage());
            Router::redirect('/nouvel_event');
        }
    }

    public function getForEdit(int $id): ?array
    {
        return $this->eventModel->findById($id);
    }

    public function handleUpdate(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect('/dashboard');
        }

        $idEvent = Security::sanitizeInt($_POST['id'] ?? 0);
        $action  = Security::sanitizeString($_POST['action'] ?? '');

        if ($idEvent <= 0) {
            Router::redirect('/dashboard');
        }

        try {
            if ($action === 'delete') {
                $existing = $this->eventModel->findById($idEvent);
                $nomEvent = $existing['nom'] ?? "#{$idEvent}";

                $this->eventModel->delete($idEvent);

                HistoriqueModel::log('delete', 'evenement', $idEvent, $nomEvent, [
                    'nom' => $nomEvent,
                ]);

                Session::set('success_msg', "L'événement a été supprimé avec succès.");
                Router::redirect('/dashboard');
            }

            if ($action === 'update') {
                $data    = $this->collectPostData();
                $erreurs = $this->validateEvent($data['nom'], $data['date_debut'], $data['date_fin']);

                if (!empty($erreurs)) {
                    Session::set('error_msg', implode('<br>', $erreurs));
                    Router::redirect("/gerer_event?id={$idEvent}");
                }

                $before = $this->eventModel->findById($idEvent);

                $this->eventModel->update($idEvent, $data);

                // Synchroniser les phases de préproduction vers le planning
                $planningModel = new PlanningModel();
                $planningModel->syncPhasesToPlanning($idEvent, [
                    'preprod'   => ['debut' => $data['date_preprod_debut'],   'fin' => $data['date_preprod_fin'],   'label' => 'Pré-production'],
                    'prod'      => ['debut' => $data['date_prod_debut'],      'fin' => $data['date_prod_fin'],      'label' => 'Production / Installation'],
                    'exploit'   => ['debut' => $data['date_exploit_debut'],   'fin' => $data['date_exploit_fin'],   'label' => 'Exploitation / Événement'],
                    'demontage' => ['debut' => $data['date_demontage_debut'], 'fin' => $data['date_demontage_fin'], 'label' => 'Démontage'],
                ]);

                HistoriqueModel::log('update', 'evenement', $idEvent, $data['nom'], [
                    'before' => array_intersect_key($before ?? [], array_flip(['nom', 'date_debut', 'date_fin', 'lieu'])),
                    'after'  => array_intersect_key($data,         array_flip(['nom', 'date_debut', 'date_fin', 'lieu'])),
                ]);

                Session::set('success_msg', "L'événement '{$data['nom']}' a été mis à jour avec succès !");
                Router::redirect('/dashboard');
            }
        } catch (\Exception $e) {
            Session::set('error_msg', 'Erreur BDD : ' . $e->getMessage());
            Router::redirect("/gerer_event?id={$idEvent}");
        }

        Router::redirect('/dashboard');
    }

    private function collectPostData(): array
    {
        $dateFinRaw = Security::sanitizeString($_POST['date_fin'] ?? '');
        $dateFin    = $dateFinRaw !== '' ? $dateFinRaw : null;
        $capacite   = isset($_POST['capacite']) && $_POST['capacite'] !== ''
                      ? Security::sanitizeInt($_POST['capacite'])
                      : null;
        $projetId   = isset($_POST['projet_id']) && $_POST['projet_id'] !== ''
                      ? Security::sanitizeInt($_POST['projet_id'])
                      : null;

        return [
            'nom'                  => Security::sanitizeString($_POST['nom_event']            ?? ''),
            'sport'                => Security::sanitizeString($_POST['type_sport']           ?? ''),
            'description'          => Security::sanitizeString($_POST['description']          ?? ''),
            'date_debut'           => Security::sanitizeString($_POST['date_debut']           ?? ''),
            'date_fin'             => $dateFin,
            'lieu'                 => Security::sanitizeString($_POST['lieu']                 ?? ''),
            'capacite'             => $capacite,
            'projet_id'            => $projetId,
            'date_preprod_debut'   => Security::sanitizeString($_POST['date_preprod_debut']   ?? '') ?: null,
            'date_preprod_fin'     => Security::sanitizeString($_POST['date_preprod_fin']     ?? '') ?: null,
            'date_prod_debut'      => Security::sanitizeString($_POST['date_prod_debut']      ?? '') ?: null,
            'date_prod_fin'        => Security::sanitizeString($_POST['date_prod_fin']        ?? '') ?: null,
            'date_exploit_debut'   => Security::sanitizeString($_POST['date_exploit_debut']   ?? '') ?: null,
            'date_exploit_fin'     => Security::sanitizeString($_POST['date_exploit_fin']     ?? '') ?: null,
            'date_demontage_debut' => Security::sanitizeString($_POST['date_demontage_debut'] ?? '') ?: null,
            'date_demontage_fin'   => Security::sanitizeString($_POST['date_demontage_fin']   ?? '') ?: null,
            'drive_url'            => Security::sanitizeString($_POST['drive_url']            ?? '') ?: null,
            'drive_doc_url'        => Security::sanitizeString($_POST['drive_doc_url']        ?? '') ?: null,
            'maps_url'             => Security::sanitizeString($_POST['maps_url']             ?? '') ?: null,
        ];
    }

    private function validateEvent(string $nom, string $dateDebut, ?string $dateFin): array
    {
        $erreurs = [];

        if (empty($nom)) {
            $erreurs[] = "Le nom de l'événement est obligatoire.";
        }
        if (empty($dateDebut)) {
            $erreurs[] = "La date de début est obligatoire.";
        }
        if ($dateFin !== null && strtotime($dateFin) < strtotime($dateDebut)) {
            $erreurs[] = "La date de fin ne peut pas être antérieure à la date de début.";
        }

        return $erreurs;
    }

    public function duplicate(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            \Core\Router::redirect('/dashboard');
        }

        $sourceId   = \Core\Security::sanitizeInt($_POST['source_id']     ?? 0);
        $nouveauNom = \Core\Security::sanitizeString($_POST['nouveau_nom'] ?? '');

        if (!$sourceId || $nouveauNom === '') {
            \Core\Session::set('error_msg', 'Nom obligatoire pour la duplication.');
            \Core\Router::redirect('/dashboard');
        }

        try {
            $ok = $this->eventModel->duplicate($sourceId, $nouveauNom);
            if ($ok) {
                try {
                    $last = $this->eventModel->getLastInsertedId();
                    HistoriqueModel::log('duplicate', 'evenement', $last, $nouveauNom, [
                        'source_id'   => $sourceId,
                        'nouveau_nom' => $nouveauNom,
                    ]);
                } catch (\Throwable) {}

                \Core\Session::set('success_msg', "Événement « {$nouveauNom} » créé par duplication !");
            } else {
                \Core\Session::set('error_msg', 'Erreur lors de la duplication.');
            }
        } catch (\Exception $e) {
            \Core\Session::set('error_msg', 'Erreur BDD : ' . $e->getMessage());
        }

        \Core\Router::redirect('/dashboard');
    }
}