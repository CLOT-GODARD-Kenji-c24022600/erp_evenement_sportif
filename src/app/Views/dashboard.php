<?php
// On importe et on inclut la connexion à la BDD
use Core\Database;

// Chemin corrigé : On remonte de deux dossiers (Views -> app -> src) pour atteindre Core
require_once __DIR__ . '/../../Core/Database.php';

// 1. RÉCUPÉRATION DES ÉVÉNEMENTS
$evenements = [];
try {
    $database = new Database();
    $db = $database->getConnection();
    
    // On sélectionne tous les événements, triés par date de début (du plus proche au plus lointain)
    $stmt = $db->query("SELECT * FROM evenements ORDER BY date_debut ASC");
    $evenements = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $erreur_bdd = "Impossible de charger les événements : " . $e->getMessage();
}
?>

<div class="container-fluid py-4">
    
    <!-- En-tête de la page -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold text-body mb-0">Tableau de bord</h1>
            <p class="text-body-secondary mb-0">Bienvenue sur l'ERP de gestion des événements sportifs.</p>
        </div>
        <a href="/?page=nouvel_event" class="btn btn-primary fw-semibold shadow-sm" style="background-color: #1a56db; border-color: #1a56db;">
            <i class="bi bi-plus-lg me-2"></i>Nouvel Événement
        </a>
    </div>

    <!-- Affichage des messages de succès (après création) -->
    <?php if (isset($_SESSION['success_msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?= $_SESSION['success_msg'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success_msg']); ?>
    <?php endif; ?>

    <!-- Affichage des erreurs BDD potentielles -->
    <?php if (isset($erreur_bdd)): ?>
        <div class="alert alert-danger shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= $erreur_bdd ?>
        </div>
    <?php endif; ?>

    <!-- 2. AFFICHAGE DYNAMIQUE DES CARTES -->
    <?php if (empty($evenements)): ?>
        <!-- État vide (si aucun événement n'est en BDD) -->
        <div class="alert alert-info shadow-sm bg-primary-subtle border-0 text-primary-emphasis" role="alert">
            <i class="bi bi-info-circle-fill me-2"></i>
            <strong>Espace vide :</strong> Les futures cartes des événements apparaîtront ici.
        </div>
    <?php else: ?>
        <!-- Grille de cartes Bootstrap -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
            <?php foreach ($evenements as $event): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm border-0 rounded-3">
                        <div class="card-body p-4">
                            
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="card-title fw-bold mb-0 text-body"><?= htmlspecialchars($event['nom']) ?></h5>
                                <!-- Affichage dynamique du sport -->
                                <span class="badge bg-primary-subtle text-primary rounded-pill fw-semibold">
                                    <?= !empty($event['sport']) ? htmlspecialchars($event['sport']) : 'Général' ?>
                                </span>
                            </div>
                            
                            <p class="card-text text-body-secondary small mb-4" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                <?= !empty($event['description']) ? nl2br(htmlspecialchars($event['description'])) : '<em>Aucune description fournie.</em>' ?>
                            </p>
                            
                            <ul class="list-unstyled mb-0 small fw-medium">
                                <li class="mb-2 text-body-secondary">
                                    <i class="bi bi-calendar-event me-2 text-primary"></i>
                                    <?= date('d/m/Y', strtotime($event['date_debut'])) ?>
                                    <?= !empty($event['date_fin']) ? ' <i class="bi bi-arrow-right mx-1"></i> ' . date('d/m/Y', strtotime($event['date_fin'])) : '' ?>
                                </li>
                                <li class="mb-2 text-body-secondary">
                                    <i class="bi bi-geo-alt me-2 text-danger"></i>
                                    <?= htmlspecialchars($event['lieu']) ?>
                                </li>
                                <!-- Affichage conditionnel de la capacité -->
                                <?php if (!empty($event['capacite'])): ?>
                                <li class="text-body-secondary">
                                    <i class="bi bi-people-fill me-2 text-success"></i>
                                    Capacité : <?= htmlspecialchars($event['capacite']) ?> places
                                </li>
                                <?php endif; ?>
                            </ul>
                            
                        </div>
                        <div class="card-footer bg-transparent border-top p-3 text-center">
                            <a href="#" class="btn btn-sm btn-outline-secondary fw-semibold w-100 rounded-3">Gérer l'événement</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>