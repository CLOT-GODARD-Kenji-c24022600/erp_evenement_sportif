<?php

/**
 * YES - Your Event Solution
 *
 * Vue : Plan du site.
 *
 * @file plan-du-site.php
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.0
 * @since 2026
 */

declare(strict_types=1);

$title = "Plan du site - YES";
?>

<section class="container py-5">
    <header class="mb-5 text-center">
        <h1 class="fw-bold text-primary">
            <i class="bi bi-diagram-3 me-2"></i> Plan du site
        </h1>
        <p class="text-muted">Retrouvez facilement toutes les rubriques de Your Event Solution.</p>
    </header>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h2 class="h5 fw-bold"><i class="bi bi-globe me-2 text-secondary"></i>Espace Public</h2>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled lh-lg">
                        <li><a href="/" class="text-decoration-none text-dark"><i class="bi bi-chevron-right small text-primary me-1"></i> Accueil</a></li>
                        <li><a href="/login" class="text-decoration-none text-dark"><i class="bi bi-chevron-right small text-primary me-1"></i> Connexion</a></li>
                        <li><a href="/inscription" class="text-decoration-none text-dark"><i class="bi bi-chevron-right small text-primary me-1"></i> Inscription</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h2 class="h5 fw-bold"><i class="bi bi-person-badge me-2 text-secondary"></i>Espace Utilisateur</h2>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled lh-lg">
                        <li><a href="/dashboard" class="text-decoration-none text-dark"><i class="bi bi-chevron-right small text-primary me-1"></i> Tableau de bord</a></li>
                        <li><a href="/profil" class="text-decoration-none text-dark"><i class="bi bi-chevron-right small text-primary me-1"></i> Mon Profil</a></li>
                        <li><a href="/evenements" class="text-decoration-none text-dark"><i class="bi bi-chevron-right small text-primary me-1"></i> Mes Évènements</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h2 class="h5 fw-bold"><i class="bi bi-shield-lock me-2 text-secondary"></i>Espace Staff</h2>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled lh-lg">
                        <li><a href="/annuaire" class="text-decoration-none text-dark"><i class="bi bi-chevron-right small text-primary me-1"></i> Annuaire du Staff</a></li>
                        <li><a href="/staff" class="text-decoration-none text-dark"><i class="bi bi-chevron-right small text-primary me-1"></i> Gestion du Staff</a></li>
                        <li><a href="/utilisateurs" class="text-decoration-none text-dark"><i class="bi bi-chevron-right small text-primary me-1"></i> Gestion des Utilisateurs</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>