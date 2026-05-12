<?php

/**
 * YES - Your Event Solution
 *
 * @file recherche.php
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.1
 * @since 2026
 *
 * Variables attendues :
 * @var string  $recherche         Terme de recherche.
 * @var array[] $resultats_staff   Résultats staff.
 * @var array[] $resultats_events  Résultats événements.
 * @var array[] $resultats_projets Résultats projets.
 * @var array   $t                 Traductions chargées.
 */

declare(strict_types=1);
?>
<section class="container-fluid">

    <header class="d-flex align-items-center mb-4">
        <i class="bi bi-search fs-3 text-primary me-3" aria-hidden="true"></i>
        <hgroup>
            <h1 class="fw-bold mb-0"><?= htmlspecialchars($t['search_title'], ENT_QUOTES) ?></h1>
            <?php if ($recherche !== ''): ?>
                <p class="text-muted mb-0">
                    <?= htmlspecialchars($t['search_query_label'], ENT_QUOTES) ?>
                    <strong>"<?= htmlspecialchars($recherche, ENT_QUOTES) ?>"</strong>
                </p>
            <?php endif; ?>
        </hgroup>
    </header>

    <?php if ($recherche === ''): ?>
        <aside class="alert alert-warning shadow-sm border-0" role="alert">
            <?= htmlspecialchars($t['search_empty_hint'], ENT_QUOTES) ?>
        </aside>

    <?php elseif (empty($resultats_staff) && empty($resultats_events) && empty($resultats_projets)): ?>
        <section class="text-center py-5 bg-white rounded shadow-sm border-0 mt-4">
            <i class="bi bi-emoji-frown fs-1 text-muted" aria-hidden="true"></i>
            <h2 class="mt-3 text-muted fw-bold h5"><?= htmlspecialchars($t['search_no_result'], ENT_QUOTES) ?></h2>
            <p class="text-muted">
                <?= htmlspecialchars($t['search_no_result_desc'], ENT_QUOTES) ?>
                "<?= htmlspecialchars($recherche, ENT_QUOTES) ?>".
            </p>
        </section>

    <?php else: ?>

        <?php if (!empty($resultats_staff)): ?>
        <section class="mb-5" aria-labelledby="staff-results">
            <h2 id="staff-results" class="fw-bold mb-3 text-primary text-uppercase search-section-title">
                <i class="bi bi-people-fill me-2" aria-hidden="true"></i>
                <?= htmlspecialchars($t['search_section_staff'], ENT_QUOTES) ?> (<?= count($resultats_staff) ?>)
            </h2>
            <ul class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4 list-unstyled">
                <?php foreach ($resultats_staff as $user): ?>
                <li class="col">
                    <article class="card h-100 border-0 shadow-sm search-card-hover">
                        <section class="card-body text-center p-4">
                            <figure class="mb-3">
                                <?php if (!empty($user['avatar'])): ?>
                                    <img src="uploads/avatars/<?= htmlspecialchars((string) $user['avatar'], ENT_QUOTES) ?>"
                                         alt="Avatar"
                                         class="rounded-circle shadow-sm search-avatar">
                                <?php else: ?>
                                    <span class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto shadow-sm search-avatar">
                                        <?= strtoupper(substr((string) $user['nom'], 0, 1)) ?>
                                    </span>
                                <?php endif; ?>
                            </figure>
                            <h3 class="fw-bold mb-1 h5">
                                <?= htmlspecialchars(trim(($user['prenom'] ?? '') . ' ' . $user['nom']), ENT_QUOTES) ?>
                            </h3>
                            <p class="text-muted small mb-0">
                                <?= htmlspecialchars((string) ($user['poste'] ?? 'Staff'), ENT_QUOTES) ?>
                            </p>
                            <a href="mailto:<?= htmlspecialchars((string) $user['email'], ENT_QUOTES) ?>"
                               class="btn btn-sm btn-outline-primary mt-3 px-4 rounded-pill">
                                <i class="bi bi-envelope me-1" aria-hidden="true"></i>
                                <?= htmlspecialchars($t['search_btn_contact'], ENT_QUOTES) ?>
                            </a>
                        </section>
                    </article>
                </li>
                <?php endforeach; ?>
            </ul>
        </section>
        <?php endif; ?>

        <?php if (!empty($resultats_projets)): ?>
        <section class="mb-5" aria-labelledby="projets-results">
            <h2 id="projets-results" class="fw-bold mb-3 text-warning text-uppercase search-section-title">
                <i class="bi bi-folder-fill me-2" aria-hidden="true"></i>
                <?= htmlspecialchars($t['search_section_projets'], ENT_QUOTES) ?> (<?= count($resultats_projets) ?>)
            </h2>
            <ul class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4 list-unstyled">
                <?php foreach ($resultats_projets as $projet): ?>
                <li class="col">
                    <article class="card h-100 border-0 shadow-sm search-card-hover position-relative">
                        <section class="card-body p-4">
                            <header class="d-flex align-items-center mb-3">
                                <span class="bg-warning bg-opacity-10 text-warning rounded p-3 me-3">
                                    <i class="bi bi-folder fs-4" aria-hidden="true"></i>
                                </span>
                                <h3 class="fw-bold mb-0 h5"><?= htmlspecialchars((string) $projet['nom'], ENT_QUOTES) ?></h3>
                            </header>
                            <p class="text-muted small mb-3">
                                <?= htmlspecialchars(mb_strimwidth((string) ($projet['description'] ?? ''), 0, 100, '...'), ENT_QUOTES) ?>
                            </p>
                            <?php if (!empty($projet['date_creation'])): ?>
                                <span class="badge bg-light text-dark border">
                                    <i class="bi bi-clock me-1" aria-hidden="true"></i>
                                    <?= date('d/m/Y', strtotime((string) $projet['date_creation'])) ?>
                                </span>
                            <?php endif; ?>
                        </section>
                        <footer class="card-footer bg-white border-top-0 pb-4 px-4">
                            <a href="/projet_detail?id=<?= (int) $projet['id'] ?>"
                               class="btn btn-sm btn-warning px-4 rounded-pill stretched-link text-white fw-bold">
                                <?= htmlspecialchars($t['search_btn_open'], ENT_QUOTES) ?>
                            </a>
                        </footer>
                    </article>
                </li>
                <?php endforeach; ?>
            </ul>
        </section>
        <?php endif; ?>

        <?php if (!empty($resultats_events)): ?>
        <section class="mb-5" aria-labelledby="events-results">
            <h2 id="events-results" class="fw-bold mb-3 text-success text-uppercase search-section-title">
                <i class="bi bi-calendar-event-fill me-2" aria-hidden="true"></i>
                <?= htmlspecialchars($t['search_section_events'], ENT_QUOTES) ?> (<?= count($resultats_events) ?>)
            </h2>
            <ul class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4 list-unstyled">
                <?php foreach ($resultats_events as $event): ?>
                <li class="col">
                    <article class="card h-100 border-0 shadow-sm search-card-hover position-relative">
                        <section class="card-body p-4">
                            <header class="d-flex align-items-center mb-3">
                                <span class="bg-success bg-opacity-10 text-success rounded p-3 me-3">
                                    <i class="bi bi-calendar-check fs-4" aria-hidden="true"></i>
                                </span>
                                <hgroup>
                                    <h3 class="fw-bold mb-1 h5"><?= htmlspecialchars((string) $event['nom'], ENT_QUOTES) ?></h3>
                                    <?php if (!empty($event['lieu'])): ?>
                                        <small class="text-muted">
                                            <i class="bi bi-geo-alt-fill me-1" aria-hidden="true"></i>
                                            <?= htmlspecialchars((string) $event['lieu'], ENT_QUOTES) ?>
                                        </small>
                                    <?php endif; ?>
                                </hgroup>
                            </header>
                            <p class="text-muted small mb-3">
                                <?= htmlspecialchars(mb_strimwidth((string) ($event['description'] ?? ''), 0, 100, '...'), ENT_QUOTES) ?>
                            </p>
                            <?php if (!empty($event['date_debut'])): ?>
                                <span class="badge bg-success bg-opacity-10 text-success">
                                    <i class="bi bi-calendar me-1" aria-hidden="true"></i>
                                    <?= date('d/m/Y', strtotime((string) $event['date_debut'])) ?>
                                </span>
                            <?php endif; ?>
                        </section>
                        <footer class="card-footer bg-white border-top-0 pb-4 px-4">
                            <a href="/gerer_event?id=<?= (int) $event['id'] ?>"
                               class="btn btn-sm btn-success px-4 rounded-pill stretched-link">
                                <?= htmlspecialchars($t['search_btn_details'], ENT_QUOTES) ?>
                            </a>
                        </footer>
                    </article>
                </li>
                <?php endforeach; ?>
            </ul>
        </section>
        <?php endif; ?>

    <?php endif; ?>

</section>