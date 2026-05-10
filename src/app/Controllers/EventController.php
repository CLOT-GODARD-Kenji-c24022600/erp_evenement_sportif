<?php

/**
 * YES - Your Event Solution
 *
 * ERP évènementiel
 *
 * @file EventController.php
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.0
 * @since 2026
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Models\EventModel;
use Core\Router;
use Core\Security;
use Core\Session;

/**
 * Contrôleur de gestion des événements.
 *
 * Gère la création, la mise à jour et la suppression des événements sportifs.
 */
class EventController
{
    private EventModel $eventModel;

    /**
     * @param EventModel $eventModel Modèle événements.
     */
    public function __construct(EventModel $eventModel)
    {
        $this->eventModel = $eventModel;
    }

    /**
     * Crée un nouvel événement à partir des données POST.
     *
     * @return void
     */
    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect('/?page=dashboard');
        }

        $nom        = Security::sanitizeString($_POST['nom_event']    ?? '');
        $sport      = Security::sanitizeString($_POST['type_sport']   ?? '');
        $description = Security::sanitizeString($_POST['description'] ?? '');
        $dateDebut  = Security::sanitizeString($_POST['date_debut']   ?? '');
        $lieu       = Security::sanitizeString($_POST['lieu']         ?? '');
        $dateFinRaw = Security::sanitizeString($_POST['date_fin']     ?? '');
        $dateFin    = $dateFinRaw !== '' ? $dateFinRaw : null;
        $capacite   = isset($_POST['capacite']) && $_POST['capacite'] !== ''
                      ? Security::sanitizeInt($_POST['capacite'])
                      : null;

        $erreurs = $this->validateEvent($nom, $dateDebut, $dateFin);

        if (!empty($erreurs)) {
            Session::set('error_msg', implode('<br>', $erreurs));
            Router::redirect('/?page=nouvel_event');
        }

        try {
            $this->eventModel->create([
                'nom'         => $nom,
                'sport'       => $sport,
                'description' => $description,
                'date_debut'  => $dateDebut,
                'date_fin'    => $dateFin,
                'lieu'        => $lieu,
                'capacite'    => $capacite,
                'projet_id'   => 1,
            ]);

            Session::set('success_msg', "L'événement '{$nom}' a été enregistré avec succès !");
            Router::redirect('/?page=dashboard');
        } catch (\Exception $e) {
            Session::set('error_msg', 'Erreur BDD : ' . $e->getMessage());
            Router::redirect('/?page=nouvel_event');
        }
    }

    /**
     * Charge un événement par son identifiant pour l'édition.
     *
     * @param int $id Identifiant de l'événement.
     * @return array|null Données de l'événement, ou null si introuvable.
     */
    public function getForEdit(int $id): ?array
    {
        return $this->eventModel->findById($id);
    }

    /**
     * Met à jour ou supprime un événement selon l'action POST.
     *
     * @return void
     */
    public function handleUpdate(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect('/?page=dashboard');
        }

        $idEvent = Security::sanitizeInt($_POST['id'] ?? 0);
        $action  = Security::sanitizeString($_POST['action'] ?? '');

        if ($idEvent <= 0) {
            Router::redirect('/?page=dashboard');
        }

        try {
            if ($action === 'delete') {
                $this->eventModel->delete($idEvent);
                Session::set('success_msg', "L'événement a été supprimé avec succès.");
                Router::redirect('/?page=dashboard');
            }

            if ($action === 'update') {
                $nom        = Security::sanitizeString($_POST['nom_event']    ?? '');
                $sport      = Security::sanitizeString($_POST['type_sport']   ?? '');
                $description = Security::sanitizeString($_POST['description'] ?? '');
                $dateDebut  = Security::sanitizeString($_POST['date_debut']   ?? '');
                $lieu       = Security::sanitizeString($_POST['lieu']         ?? '');
                $dateFinRaw = Security::sanitizeString($_POST['date_fin']     ?? '');
                $dateFin    = $dateFinRaw !== '' ? $dateFinRaw : null;
                $capacite   = isset($_POST['capacite']) && $_POST['capacite'] !== ''
                              ? Security::sanitizeInt($_POST['capacite'])
                              : null;

                $erreurs = $this->validateEvent($nom, $dateDebut, $dateFin);

                if (!empty($erreurs)) {
                    Session::set('error_msg', implode('<br>', $erreurs));
                    Router::redirect("/?page=gerer_event&id={$idEvent}");
                }

                $this->eventModel->update($idEvent, [
                    'nom'         => $nom,
                    'sport'       => $sport,
                    'description' => $description,
                    'date_debut'  => $dateDebut,
                    'date_fin'    => $dateFin,
                    'lieu'        => $lieu,
                    'capacite'    => $capacite,
                ]);

                Session::set('success_msg', "L'événement '{$nom}' a été mis à jour avec succès !");
                Router::redirect('/?page=dashboard');
            }
        } catch (\Exception $e) {
            Session::set('error_msg', 'Erreur BDD : ' . $e->getMessage());
            Router::redirect("/?page=gerer_event&id={$idEvent}");
        }

        Router::redirect('/?page=dashboard');
    }

    /**
     * Valide les données d'un événement.
     *
     * @param string      $nom       Nom de l'événement.
     * @param string      $dateDebut Date de début.
     * @param string|null $dateFin   Date de fin (optionnelle).
     * @return string[]              Liste des erreurs.
     */
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
}
