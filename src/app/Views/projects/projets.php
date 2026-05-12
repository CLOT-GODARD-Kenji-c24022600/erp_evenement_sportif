<?php
declare(strict_types=1);

$statuts = [
    'en_cours'   => ['label' => 'En cours',   'color' => 'primary'],
    'en_attente' => ['label' => 'En attente', 'color' => 'warning'],
    'termine'    => ['label' => 'Terminé',    'color' => 'success'],
    'archive'    => ['label' => 'Archivé',    'color' => 'secondary'],
];
?>
<div class="container-fluid py-4">

    <header class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <hgroup>
            <h1 class="fw-bold text-body mb-0">
                <i class="bi bi-kanban-fill me-2 text-primary" aria-hidden="true"></i>
                <?= htmlspecialchars($t['nav_projects'], ENT_QUOTES) ?>
            </h1>
            <p class="text-body-secondary mb-0"><?= htmlspecialchars($t['project_subtitle'], ENT_QUOTES) ?></p>
        </hgroup>
        <button class="btn btn-primary fw-semibold shadow-sm"
                data-bs-toggle="modal" data-bs-target="#modalNewProject">
            <i class="bi bi-plus-lg me-2" aria-hidden="true"></i>
            <?= htmlspecialchars($t['project_new_btn'], ENT_QUOTES) ?>
        </button>
    </header>

    <?php if ($projetMsg !== null): ?>
        <aside class="alert alert-<?= $projetType === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show shadow-sm mb-4" role="alert">
            <i class="bi bi-<?= $projetType === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill' ?> me-2"></i>
            <?= htmlspecialchars((string) $projetMsg, ENT_QUOTES) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </aside>
    <?php endif; ?>

    <?php if (empty($projets)): ?>
        <aside class="alert alert-info bg-primary-subtle border-0 shadow-sm text-primary-emphasis" role="alert">
            <i class="bi bi-info-circle-fill me-2"></i>
            <?= htmlspecialchars($t['project_empty'], ENT_QUOTES) ?>
        </aside>
    <?php else: ?>
        <ul class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4 list-unstyled">
            <?php foreach ($projets as $p):
                $recettes   = (float) ($p['total_recettes'] ?? 0);
                $depenses   = (float) ($p['total_depenses'] ?? 0);
                $budget     = (float) ($p['budget']         ?? 0);
                $solde      = $recettes - $depenses;
                $statutInfo = $statuts[$p['statut']] ?? $statuts['en_cours'];
            ?>
            <li class="col">
                <article class="card h-100 shadow-sm border-0 rounded-3 project-card">
                    <div class="card-body p-4">
                        <header class="d-flex justify-content-between align-items-start mb-3">
                            <h2 class="card-title fw-bold mb-0 fs-6">
                                <?= htmlspecialchars((string) $p['nom'], ENT_QUOTES) ?>
                            </h2>
                            <span class="badge bg-<?= $statutInfo['color'] ?>-subtle text-<?= $statutInfo['color'] ?> rounded-pill fw-semibold">
                                <?= htmlspecialchars($statutInfo['label'], ENT_QUOTES) ?>
                            </span>
                        </header>

                        <?php if (!empty($p['description'])): ?>
                            <p class="card-text text-body-secondary small mb-3 project-desc">
                                <?= nl2br(htmlspecialchars((string) $p['description'], ENT_QUOTES)) ?>
                            </p>
                        <?php endif; ?>

                        <ul class="list-unstyled small mb-4">
                            <?php if ($budget > 0): ?>
                            <li class="mb-1 text-body-secondary">
                                <i class="bi bi-wallet2 me-2 text-primary"></i>
                                <?= htmlspecialchars($t['project_budget'], ENT_QUOTES) ?> :
                                <strong><?= number_format($budget, 2, ',', ' ') ?> €</strong>
                            </li>
                            <?php endif; ?>
                            <li class="mb-1 text-body-secondary">
                                <i class="bi bi-arrow-up-circle me-2 text-success"></i>
                                <?= htmlspecialchars($t['project_recettes'], ENT_QUOTES) ?> :
                                <strong class="text-success"><?= number_format($recettes, 2, ',', ' ') ?> €</strong>
                            </li>
                            <li class="mb-1 text-body-secondary">
                                <i class="bi bi-arrow-down-circle me-2 text-danger"></i>
                                <?= htmlspecialchars($t['project_depenses'], ENT_QUOTES) ?> :
                                <strong class="text-danger"><?= number_format($depenses, 2, ',', ' ') ?> €</strong>
                            </li>
                            <li class="mb-1 text-body-secondary">
                                <i class="bi bi-calculator me-2 text-info"></i>
                                <?= htmlspecialchars($t['project_solde'], ENT_QUOTES) ?> :
                                <strong class="<?= $solde >= 0 ? 'text-success' : 'text-danger' ?>">
                                    <?= number_format($solde, 2, ',', ' ') ?> €
                                </strong>
                            </li>
                            <?php if (!empty($p['nb_evenements'])): ?>
                            <li class="text-body-secondary">
                                <i class="bi bi-calendar-event me-2 text-warning"></i>
                                <?= (int) $p['nb_evenements'] ?> <?= htmlspecialchars($t['project_linked_events'], ENT_QUOTES) ?>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <footer class="card-footer bg-transparent border-top p-3 d-flex gap-2">
                        <a href="/projet_detail?id=<?= (int) $p['id'] ?>"
                           class="btn btn-sm btn-primary fw-semibold flex-grow-1 rounded-3">
                            <i class="bi bi-eye me-1"></i>
                            <?= htmlspecialchars($t['project_detail_btn'], ENT_QUOTES) ?>
                        </a>
                        <button class="btn btn-sm btn-outline-secondary rounded-3"
                                data-bs-toggle="modal" data-bs-target="#modalEditProject"
                                data-id="<?= (int) $p['id'] ?>"
                                data-nom="<?= htmlspecialchars((string) $p['nom'], ENT_QUOTES) ?>"
                                data-description="<?= htmlspecialchars((string) ($p['description'] ?? ''), ENT_QUOTES) ?>"
                                data-statut="<?= htmlspecialchars((string) $p['statut'], ENT_QUOTES) ?>"
                                data-budget="<?= htmlspecialchars((string) ($p['budget'] ?? ''), ENT_QUOTES) ?>"
                                data-date_debut="<?= htmlspecialchars((string) ($p['date_debut'] ?? ''), ENT_QUOTES) ?>"
                                data-date_fin="<?= htmlspecialchars((string) ($p['date_fin'] ?? ''), ENT_QUOTES) ?>">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger rounded-3"
                                data-bs-toggle="modal" data-bs-target="#modalDeleteProject"
                                data-id="<?= (int) $p['id'] ?>"
                                data-nom="<?= htmlspecialchars((string) $p['nom'], ENT_QUOTES) ?>">
                            <i class="bi bi-trash"></i>
                        </button>
                    </footer>
                </article>
            </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<!-- Modale : Nouveau projet -->
<dialog id="modalNewProject" class="modal fade" tabindex="-1" aria-modal="true">
    <section class="modal-dialog">
        <article class="modal-content">
            <form method="POST" action="">
                <input type="hidden" name="project_action" value="create">
                <header class="modal-header border-0 pb-0">
                    <h2 class="modal-title fw-bold fs-5" id="modalNewProjectLabel">
                        <i class="bi bi-kanban-fill me-2 text-primary"></i>
                        <?= htmlspecialchars($t['project_modal_create_title'], ENT_QUOTES) ?>
                    </h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </header>
                <div class="modal-body">
                    <p class="mb-3">
                        <label for="np-nom" class="form-label small fw-bold">
                            <?= htmlspecialchars($t['project_field_name'], ENT_QUOTES) ?> <abbr title="obligatoire" class="text-danger">*</abbr>
                        </label>
                        <input type="text" id="np-nom" name="nom" class="form-control" placeholder="Ex : Festival Été 2026" required>
                    </p>
                    <p class="mb-3">
                        <label for="np-desc" class="form-label small fw-bold"><?= htmlspecialchars($t['project_field_desc'], ENT_QUOTES) ?></label>
                        <textarea id="np-desc" name="description" class="form-control" rows="3"></textarea>
                    </p>
                    <section class="row mb-3">
                        <p class="col mb-0">
                            <label for="np-statut" class="form-label small fw-bold"><?= htmlspecialchars($t['project_field_status'], ENT_QUOTES) ?></label>
                            <select id="np-statut" name="statut" class="form-select">
                                <?php foreach ($statuts as $key => $s): ?>
                                    <option value="<?= $key ?>"><?= htmlspecialchars($s['label'], ENT_QUOTES) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </p>
                        <p class="col mb-0">
                            <label for="np-budget" class="form-label small fw-bold"><?= htmlspecialchars($t['project_field_budget'], ENT_QUOTES) ?> (€)</label>
                            <input type="number" id="np-budget" name="budget" class="form-control" min="0" step="0.01" placeholder="0.00">
                        </p>
                    </section>
                    <section class="row mb-3">
                        <p class="col mb-0">
                            <label for="np-debut" class="form-label small fw-bold"><?= htmlspecialchars($t['form_start_date'], ENT_QUOTES) ?></label>
                            <input type="date" id="np-debut" name="date_debut" class="form-control">
                        </p>
                        <p class="col mb-0">
                            <label for="np-fin" class="form-label small fw-bold"><?= htmlspecialchars($t['form_end_date'], ENT_QUOTES) ?></label>
                            <input type="date" id="np-fin" name="date_fin" class="form-control">
                        </p>
                    </section>
                </div>
                <footer class="modal-footer border-0 pt-0">
                    <button type="submit" class="btn btn-primary w-100 fw-bold">
                        <i class="bi bi-check-lg me-2"></i><?= htmlspecialchars($t['project_create_btn'], ENT_QUOTES) ?>
                    </button>
                </footer>
            </form>
        </article>
    </section>
</dialog>

<!-- Modale : Modifier projet -->
<dialog id="modalEditProject" class="modal fade" tabindex="-1" aria-modal="true">
    <section class="modal-dialog">
        <article class="modal-content">
            <form method="POST" action="">
                <input type="hidden" name="project_action" value="edit">
                <input type="hidden" name="projet_id" id="ep-id">
                <header class="modal-header border-0 pb-0">
                    <h2 class="modal-title fw-bold fs-5">
                        <i class="bi bi-pencil-square me-2 text-warning"></i>
                        <?= htmlspecialchars($t['project_modal_edit_title'], ENT_QUOTES) ?>
                    </h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </header>
                <div class="modal-body">
                    <p class="mb-3">
                        <label for="ep-nom" class="form-label small fw-bold"><?= htmlspecialchars($t['project_field_name'], ENT_QUOTES) ?> <abbr title="obligatoire" class="text-danger">*</abbr></label>
                        <input type="text" id="ep-nom" name="nom" class="form-control" required>
                    </p>
                    <p class="mb-3">
                        <label for="ep-desc" class="form-label small fw-bold"><?= htmlspecialchars($t['project_field_desc'], ENT_QUOTES) ?></label>
                        <textarea id="ep-desc" name="description" class="form-control" rows="3"></textarea>
                    </p>
                    <section class="row mb-3">
                        <p class="col mb-0">
                            <label for="ep-statut" class="form-label small fw-bold"><?= htmlspecialchars($t['project_field_status'], ENT_QUOTES) ?></label>
                            <select id="ep-statut" name="statut" class="form-select">
                                <?php foreach ($statuts as $key => $s): ?>
                                    <option value="<?= $key ?>"><?= htmlspecialchars($s['label'], ENT_QUOTES) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </p>
                        <p class="col mb-0">
                            <label for="ep-budget" class="form-label small fw-bold"><?= htmlspecialchars($t['project_field_budget'], ENT_QUOTES) ?> (€)</label>
                            <input type="number" id="ep-budget" name="budget" class="form-control" min="0" step="0.01">
                        </p>
                    </section>
                    <section class="row mb-3">
                        <p class="col mb-0">
                            <label for="ep-debut" class="form-label small fw-bold"><?= htmlspecialchars($t['form_start_date'], ENT_QUOTES) ?></label>
                            <input type="date" id="ep-debut" name="date_debut" class="form-control">
                        </p>
                        <p class="col mb-0">
                            <label for="ep-fin" class="form-label small fw-bold"><?= htmlspecialchars($t['form_end_date'], ENT_QUOTES) ?></label>
                            <input type="date" id="ep-fin" name="date_fin" class="form-control">
                        </p>
                    </section>
                </div>
                <footer class="modal-footer border-0 pt-0">
                    <button type="submit" class="btn btn-warning w-100 fw-bold">
                        <i class="bi bi-save me-2"></i><?= htmlspecialchars($t['btn_save'], ENT_QUOTES) ?>
                    </button>
                </footer>
            </form>
        </article>
    </section>
</dialog>

<!-- Modale : Supprimer projet -->
<dialog id="modalDeleteProject" class="modal fade" tabindex="-1" aria-modal="true">
    <section class="modal-dialog">
        <article class="modal-content">
            <form method="POST" action="">
                <input type="hidden" name="project_action" value="delete">
                <input type="hidden" name="projet_id" id="dp-id">
                <header class="modal-header border-0 pb-0">
                    <h2 class="modal-title fw-bold fs-5 text-danger">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <?= htmlspecialchars($t['project_modal_delete_title'], ENT_QUOTES) ?>
                    </h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </header>
                <div class="modal-body">
                    <p class="mb-0">
                        <?= htmlspecialchars($t['project_delete_confirm'], ENT_QUOTES) ?>
                        <strong id="dp-nom" class="text-danger"></strong> ?
                    </p>
                    <p class="text-muted small mt-2 mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        <?= htmlspecialchars($t['project_delete_warning'], ENT_QUOTES) ?>
                    </p>
                </div>
                <footer class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <?= htmlspecialchars($t['btn_cancel'], ENT_QUOTES) ?>
                    </button>
                    <button type="submit" class="btn btn-danger fw-bold">
                        <i class="bi bi-trash me-2"></i><?= htmlspecialchars($t['btn_delete'], ENT_QUOTES) ?>
                    </button>
                </footer>
            </form>
        </article>
    </section>
</dialog>