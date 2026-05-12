<?php

/**
 * YES - Your Event Solution
 *
 * Vue : Tableau de bord principal.
 *
 * @file dashboard.php
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.0
 * @since 2026
 *
 * Variables attendues (injectées par index.php via DashboardController) :
 * @var array       $evenements   Liste des événements.
 * @var array       $todos        Liste des tâches.
 * @var array       $todoStats    Statistiques des tâches.
 * @var array       $utilisateurs Liste des utilisateurs approuvés.
 * @var string|null $todoMsg      Message flash todo.
 * @var string      $todoType     Type du message ('success' ou 'error').
 * @var string|null $erreur_bdd   Message d'erreur BDD.
 * @var string      $dbStatus     Statut de connexion BDD.
 * @var array       $t            Traductions chargées.
 */

declare(strict_types=1);
?>
<div class="container-fluid py-4">

    <header class="mb-4">
        <h1 class="fw-bold text-body mb-0"><?= htmlspecialchars($t['nav_dashboard'], ENT_QUOTES) ?></h1>
        <p class="text-body-secondary mb-0"><?= htmlspecialchars($t['dash_welcome'], ENT_QUOTES) ?></p>
    </header>

    <?php if ($todoMsg !== null): ?>
        <aside class="alert alert-<?= $todoType === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show shadow-sm mb-4" role="alert">
            <i class="bi bi-<?= $todoType === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill' ?> me-2" aria-hidden="true"></i>
            <?= htmlspecialchars((string) $todoMsg, ENT_QUOTES) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="<?= htmlspecialchars($t['btn_close'], ENT_QUOTES) ?>"></button>
        </aside>
    <?php endif; ?>

    <?php if (isset($_SESSION['success_msg'])): ?>
        <aside class="alert alert-success alert-dismissible fade show shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2" aria-hidden="true"></i>
            <?= htmlspecialchars((string) $_SESSION['success_msg'], ENT_QUOTES) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="<?= htmlspecialchars($t['btn_close'], ENT_QUOTES) ?>"></button>
        </aside>
        <?php unset($_SESSION['success_msg']); ?>
    <?php endif; ?>

    <?php if (isset($erreur_bdd) && $erreur_bdd !== null): ?>
        <aside class="alert alert-danger shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2" aria-hidden="true"></i>
            <?= htmlspecialchars((string) $erreur_bdd, ENT_QUOTES) ?>
        </aside>
    <?php endif; ?>

    <!-- Todolist -->
    <?php include __DIR__ . '/todolist.php'; ?>

    <hr class="my-5 opacity-25">

    <!-- Événements -->
    <section aria-labelledby="events-heading">

        <header class="d-flex justify-content-between align-items-center mb-3">
            <h2 id="events-heading" class="fw-bold fs-5 mb-0">
                <i class="bi bi-calendar-event me-2 text-primary" aria-hidden="true"></i>
                <?= htmlspecialchars($t['dash_events_title'], ENT_QUOTES) ?>
            </h2>
            <a href="/nouvel_event" class="btn btn-primary fw-semibold shadow-sm">
                <i class="bi bi-plus-lg me-2" aria-hidden="true"></i>
                <?= htmlspecialchars($t['nav_new_event'], ENT_QUOTES) ?>
            </a>
        </header>

        <?php if (empty($evenements)): ?>
            <aside class="alert alert-info shadow-sm bg-primary-subtle border-0 text-primary-emphasis" role="alert">
                <i class="bi bi-info-circle-fill me-2" aria-hidden="true"></i>
                <strong><?= htmlspecialchars($t['dash_empty_title'], ENT_QUOTES) ?></strong>
                <?= htmlspecialchars($t['dash_empty_desc'], ENT_QUOTES) ?>
            </aside>
        <?php else: ?>
            <ul class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4 list-unstyled">
                <?php foreach ($evenements as $event): ?>
                <li class="col">
                    <article class="card h-100 shadow-sm border-0 rounded-3">
                        <div class="card-body p-4">
                            <header class="d-flex justify-content-between align-items-start mb-3">
                                <h3 class="card-title fw-bold mb-0 fs-6 text-body">
                                    <?= htmlspecialchars((string) $event['nom'], ENT_QUOTES) ?>
                                </h3>
                                <span class="badge bg-primary-subtle text-primary rounded-pill fw-semibold">
                                    <?= !empty($event['sport'])
                                        ? htmlspecialchars((string) $event['sport'], ENT_QUOTES)
                                        : htmlspecialchars($t['label_none'], ENT_QUOTES) ?>
                                </span>
                            </header>
                            <p class="card-text text-body-secondary small mb-4 event-desc">
                                <?= !empty($event['description'])
                                    ? nl2br(htmlspecialchars((string) $event['description'], ENT_QUOTES))
                                    : '<em>' . htmlspecialchars($t['dash_no_events'], ENT_QUOTES) . '</em>' ?>
                            </p>
                            <ul class="list-unstyled mb-0 small fw-medium">
                                <li class="mb-2 text-body-secondary">
                                    <i class="bi bi-calendar-event me-2 text-primary" aria-hidden="true"></i>
                                    <time datetime="<?= htmlspecialchars((string) $event['date_debut'], ENT_QUOTES) ?>">
                                        <?= date('d/m/Y', strtotime((string) $event['date_debut'])) ?>
                                    </time>
                                    <?php if (!empty($event['date_fin'])): ?>
                                        <i class="bi bi-arrow-right mx-1" aria-hidden="true"></i>
                                        <time datetime="<?= htmlspecialchars((string) $event['date_fin'], ENT_QUOTES) ?>">
                                            <?= date('d/m/Y', strtotime((string) $event['date_fin'])) ?>
                                        </time>
                                    <?php endif; ?>
                                </li>
                                <li class="mb-2 text-body-secondary">
                                    <i class="bi bi-geo-alt me-2 text-danger" aria-hidden="true"></i>
                                    <?= htmlspecialchars((string) $event['lieu'], ENT_QUOTES) ?>
                                </li>
                                <?php if (!empty($event['capacite'])): ?>
                                <li class="text-body-secondary">
                                    <i class="bi bi-people-fill me-2 text-success" aria-hidden="true"></i>
                                    <?= htmlspecialchars($t['form_capacity'], ENT_QUOTES) ?> :
                                    <?= htmlspecialchars((string) $event['capacite'], ENT_QUOTES) ?>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                        <footer class="card-footer bg-transparent border-top p-3 text-center">
                            <a href="/gerer_event?id=<?= (int) $event['id'] ?>"
                               class="btn btn-sm btn-outline-secondary fw-semibold w-100 rounded-3">
                                <?= htmlspecialchars($t['dash_manage_btn'], ENT_QUOTES) ?>
                            </a>
                        </footer>
                    </article>
                </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

    </section>

</div>