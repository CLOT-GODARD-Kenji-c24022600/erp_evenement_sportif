<?php

/**
 * YES - Your Event Solution
 *
 * ERP évènementiel
 *
 * @file SearchController.php
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.0
 * @since 2026
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Models\SearchModel;
use Core\Security;

/**
 * Contrôleur de recherche globale.
 *
 * Orchestre la recherche dans le staff, les événements et les projets.
 */
class SearchController
{
    private SearchModel $searchModel;

    /**
     * @param SearchModel $searchModel Modèle de recherche.
     */
    public function __construct(SearchModel $searchModel)
    {
        $this->searchModel = $searchModel;
    }

    /**
     * Effectue la recherche et retourne les résultats.
     *
     * @param string $query Terme de recherche brut.
     * @return array{
     *   recherche: string,
     *   resultats_staff: array,
     *   resultats_events: array,
     *   resultats_projets: array
     * }
     */
    public function search(string $query): array
    {
        $recherche = Security::sanitizeString($query);

        if (empty($recherche)) {
            return [
                'recherche'        => '',
                'resultats_staff'  => [],
                'resultats_events' => [],
                'resultats_projets'=> [],
            ];
        }

        return [
            'recherche'         => $recherche,
            'resultats_staff'   => $this->searchModel->searchStaff($recherche),
            'resultats_events'  => $this->searchModel->searchEvents($recherche),
            'resultats_projets' => $this->searchModel->searchProjets($recherche),
        ];
    }
}
