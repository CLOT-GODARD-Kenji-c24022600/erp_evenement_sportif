<?php

use Core\Database;
use App\Models\TodoModel;
use App\Controllers\TodoController;

// Traitement des actions Todo (POST)
$todoMsg  = null;
$todoType = 'success';

$todoController = new TodoController();
$result = $todoController->handleRequest();
if ($result !== null) {
    [$todoType, $todoMsg] = explode(':', $result, 2);
}

// Chargement des evenements
$evenements = [];
try {
    $db   = Database::getConnection();
    $stmt = $db->query("SELECT * FROM evenements ORDER BY date_debut ASC");
    $evenements = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $erreur_bdd = "Impossible de charger les evenements : " . $e->getMessage();
}

// Chargement des todos et utilisateurs
$todos        = [];
$todoStats    = ['total' => 0, 'done' => 0, 'en_cours' => 0, 'en_attente' => 0];
$utilisateurs = [];
try {
    $todoModel    = new TodoModel();
    $todos        = $todoModel->getAllTodos();
    $todoStats    = $todoModel->getStats();
    $utilisateurs = $todoModel->getUtilisateurs();
} catch (Exception $e) {
    // Table inexistante : migration SQL pas encore jouee
}
?>

<div class="container-fluid py-4">

    <!-- En-tete de page -->
    <header class="mb-4">
        <h1 class="fw-bold text-body mb-0">Tableau de bord</h1>
        <p class="text-body-secondary mb-0">Bienvenue sur l'ERP de gestion des événements sportifs.</p>
    </header>

    <!-- Message flash Todo -->
    <?php if ($todoMsg !== null): ?>
        <aside class="alert alert-<?= $todoType === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show shadow-sm mb-4"
               role="alert">
            <i class="bi bi-<?= $todoType === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill' ?> me-2"
               aria-hidden="true"></i>
            <?= htmlspecialchars($todoMsg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        </aside>
    <?php endif; ?>

    <!-- Message succes session -->
    <?php if (isset($_SESSION['success_msg'])): ?>
        <aside class="alert alert-success alert-dismissible fade show shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2" aria-hidden="true"></i>
            <?= htmlspecialchars($_SESSION['success_msg']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        </aside>
        <?php unset($_SESSION['success_msg']); ?>
    <?php endif; ?>

    <!-- Erreur BDD -->
    <?php if (isset($erreur_bdd)): ?>
        <aside class="alert alert-danger shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2" aria-hidden="true"></i>
            <?= htmlspecialchars($erreur_bdd) ?>
        </aside>
    <?php endif; ?>

    <!-- SECTION TODOLIST -->
    <?php include __DIR__ . '/todolist.php'; ?>

    <hr class="my-5 opacity-25">

    <!-- SECTION EVENEMENTS -->
    <section aria-labelledby="events-heading">

        <header class="d-flex justify-content-between align-items-center mb-3">
            <h2 id="events-heading" class="fw-bold fs-5 mb-0">
                <i class="bi bi-calendar-event me-2 text-primary" aria-hidden="true"></i>Événements
            </h2>
            <a href="/?page=nouvel_event"
               class="btn btn-primary fw-semibold shadow-sm"
               style="background-color:#1a56db; border-color:#1a56db;">
                <i class="bi bi-plus-lg me-2" aria-hidden="true"></i>Nouvel Événement
            </a>
        </header>

        <?php if (empty($evenements)): ?>
            <aside class="alert alert-info shadow-sm bg-primary-subtle border-0 text-primary-emphasis" role="alert">
                <i class="bi bi-info-circle-fill me-2" aria-hidden="true"></i>
                <strong>Espace vide :</strong> Les futures cartes des événements apparaitront ici.
            </aside>

        <?php else: ?>
            <ul class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4 list-unstyled">
                <?php foreach ($evenements as $event): ?>
                <li class="col">
                    <article class="card h-100 shadow-sm border-0 rounded-3">
                        <div class="card-body p-4">
                            <header class="d-flex justify-content-between align-items-start mb-3">
                                <h3 class="card-title fw-bold mb-0 fs-6 text-body">
                                    <?= htmlspecialchars($event['nom']) ?>
                               </h3>
                                <span class="badge bg-primary-subtle text-primary rounded-pill fw-semibold">
                                    <?= !empty($event['sport']) ? htmlspecialchars($event['sport']) : 'Général' ?>
                                </span>
                            </header>
                            <p class="card-text text-body-secondary small mb-4"
                               style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                                <?= !empty($event['description'])
                                    ? nl2br(htmlspecialchars($event['description']))
                                    : '<em>Aucune description fournie.</em>' ?>
                            </p>
                            <ul class="list-unstyled mb-0 small fw-medium">
                                <li class="mb-2 text-body-secondary">
                                    <i class="bi bi-calendar-event me-2 text-primary" aria-hidden="true"></i>
                                    <time datetime="<?= htmlspecialchars($event['date_debut']) ?>">
                                        <?= date('d/m/Y', strtotime($event['date_debut'])) ?>
                                    </time>
                                    <?php if (!empty($event['date_fin'])): ?>
                                        <i class="bi bi-arrow-right mx-1" aria-hidden="true"></i>
                                        <time datetime="<?= htmlspecialchars($event['date_fin']) ?>">
                                            <?= date('d/m/Y', strtotime($event['date_fin'])) ?>
                                        </time>
                                    <?php endif; ?>
                                </li>
                                <li class="mb-2 text-body-secondary">
                                    <i class="bi bi-geo-alt me-2 text-danger" aria-hidden="true"></i>
                                    <?= htmlspecialchars($event['lieu']) ?>
                                </li>
                                <?php if (!empty($event['capacite'])): ?>
                                <li class="text-body-secondary">
                                    <i class="bi bi-people-fill me-2 text-success" aria-hidden="true"></i>
                                    Capacité : <?= htmlspecialchars($event['capacite']) ?> places
                                </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                        <footer class="card-footer bg-transparent border-top p-3 text-center">
                            <a href="/?page=gerer_event&id=<?= (int)$event['id'] ?>"
                               class="btn btn-sm btn-outline-secondary fw-semibold w-100 rounded-3">
                                Gérer l'événement
                            </a>
                        </footer>
                    </article>
                </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

    </section>

</div>