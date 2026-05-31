<?php
declare(strict_types=1);

$statuts = [
    'en_cours'   => ['label' => 'En cours',   'color' => 'primary'],
    'en_attente' => ['label' => 'En attente', 'color' => 'warning'],
    'termine'    => ['label' => 'Terminé',    'color' => 'success'],
    'archive'    => ['label' => 'Archivé',    'color' => 'secondary'],
];
$totalProjets  = count($projets ?? []);
$totalBudget   = array_sum(array_column($projets ?? [], 'budget'));
$totalRecettes = array_sum(array_column($projets ?? [], 'total_recettes'));
$totalDepenses = array_sum(array_column($projets ?? [], 'total_depenses'));
?>
<div class="container-fluid py-4">

    <!-- ── En-tête ───────────────────────────────────────── -->
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
            <?= htmlspecialchars((string)$projetMsg, ENT_QUOTES) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </aside>
    <?php endif; ?>

    <!-- ── KPIs globaux ──────────────────────────────────── -->
    <?php if ($totalProjets > 0): ?>
    <ul class="row row-cols-2 row-cols-md-4 g-3 list-unstyled mb-4">
        <?php foreach ([
            ['label'=>'Projets',  'val'=>$totalProjets,                                      'color'=>'primary', 'icon'=>'bi-kanban-fill',      'fmt'=>false],
            ['label'=>'Budget',   'val'=>$totalBudget,                                        'color'=>'info',    'icon'=>'bi-wallet2',          'fmt'=>true],
            ['label'=>'Recettes', 'val'=>$totalRecettes,                                      'color'=>'success', 'icon'=>'bi-arrow-up-circle',  'fmt'=>true],
            ['label'=>'Dépenses', 'val'=>$totalDepenses,                                      'color'=>'danger',  'icon'=>'bi-arrow-down-circle','fmt'=>true],
        ] as $kpi): ?>
        <li class="col">
            <article class="card border-0 shadow-sm rounded-3 text-center h-100">
                <div class="card-body py-3">
                    <i class="bi <?= $kpi['icon'] ?> text-<?= $kpi['color'] ?> fs-3 d-block mb-1"></i>
                    <p class="text-body-secondary small mb-1"><?= $kpi['label'] ?></p>
                    <p class="fw-bold fs-5 mb-0 text-<?= $kpi['color'] ?>">
                        <?= $kpi['fmt'] ? number_format((float)$kpi['val'],2,',',' ').' €' : $kpi['val'] ?>
                    </p>
                </div>
            </article>
        </li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>

    <!-- ── Liste des projets ─────────────────────────────── -->
    <?php if (empty($projets)): ?>
        <aside class="alert alert-info bg-primary-subtle border-0 shadow-sm text-primary-emphasis" role="alert">
            <i class="bi bi-info-circle-fill me-2"></i>
            <?= htmlspecialchars($t['project_empty'], ENT_QUOTES) ?>
        </aside>
    <?php else: ?>
        <ul class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4 list-unstyled">
            <?php foreach ($projets as $p):
                $recettes   = (float)($p['total_recettes'] ?? 0);
                $depenses   = (float)($p['total_depenses'] ?? 0);
                $budget     = (float)($p['budget']         ?? 0);
                $solde      = $recettes - $depenses;
                $ecart      = $budget > 0 ? $budget - $depenses : null;
                $statutInfo = $statuts[$p['statut']] ?? $statuts['en_cours'];
            ?>
            <li class="col">
                <article class="card h-100 shadow-sm border-0 rounded-3">
                    <!-- Barre colorée selon statut -->
                    <div style="height:4px;background:var(--bs-<?= $statutInfo['color'] ?>);border-radius:12px 12px 0 0;"></div>
                    <div class="card-body p-4">
                        <header class="d-flex justify-content-between align-items-start mb-3">
                            <h2 class="card-title fw-bold mb-0 fs-6">
                                <?= htmlspecialchars((string)$p['nom'], ENT_QUOTES) ?>
                            </h2>
                            <span class="badge bg-<?= $statutInfo['color'] ?>-subtle text-<?= $statutInfo['color'] ?> rounded-pill fw-semibold">
                                <?= htmlspecialchars($statutInfo['label'], ENT_QUOTES) ?>
                            </span>
                        </header>

                        <?php if (!empty($p['description'])): ?>
                            <p class="card-text text-body-secondary small mb-3 project-desc">
                                <?= nl2br(htmlspecialchars((string)$p['description'], ENT_QUOTES)) ?>
                            </p>
                        <?php endif; ?>

                        <!-- Finance compacte -->
                        <div class="d-flex gap-3 mb-3 flex-wrap small">
                            <?php if ($budget > 0): ?>
                            <div class="text-body-secondary">
                                <i class="bi bi-wallet2 me-1 text-primary"></i>
                                Budget : <strong><?= number_format($budget, 0, ',', ' ') ?> €</strong>
                            </div>
                            <?php endif; ?>
                            <div class="text-body-secondary">
                                <i class="bi bi-arrow-up-circle me-1 text-success"></i>
                                <strong class="text-success"><?= number_format($recettes, 0, ',', ' ') ?> €</strong>
                            </div>
                            <div class="text-body-secondary">
                                <i class="bi bi-arrow-down-circle me-1 text-danger"></i>
                                <strong class="text-danger"><?= number_format($depenses, 0, ',', ' ') ?> €</strong>
                            </div>
                        </div>

                        <!-- Solde + barre de progression si budget défini -->
                        <?php if ($budget > 0): ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="text-body-secondary">Consommation budget</span>
                                <span class="fw-bold <?= $depenses > $budget ? 'text-danger' : 'text-success' ?>">
                                    <?= $budget > 0 ? round($depenses/$budget*100) : 0 ?>%
                                </span>
                            </div>
                            <div class="progress rounded-pill" style="height:6px;">
                                <div class="progress-bar bg-<?= $depenses > $budget ? 'danger' : 'success' ?>"
                                     style="width:<?= min(100, $budget > 0 ? round($depenses/$budget*100) : 0) ?>%"></div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="d-flex align-items-center justify-content-between">
                            <span class="fw-bold <?= $solde >= 0 ? 'text-success' : 'text-danger' ?> small">
                                <i class="bi bi-calculator me-1"></i>
                                Solde : <?= ($solde >= 0 ? '+' : '') . number_format($solde, 2, ',', ' ') ?> €
                            </span>
                            <?php if (!empty($p['nb_evenements'])): ?>
                            <span class="badge bg-warning-subtle text-warning rounded-pill small">
                                <i class="bi bi-calendar-event me-1"></i>
                                <?= (int)$p['nb_evenements'] ?> événement<?= $p['nb_evenements'] > 1 ? 's' : '' ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <footer class="card-footer bg-transparent border-top p-3 d-flex gap-2">
                        <a href="/projet_detail?id=<?= (int)$p['id'] ?>"
                           class="btn btn-sm btn-primary fw-semibold flex-grow-1 rounded-3">
                            <i class="bi bi-eye me-1"></i>
                            <?= htmlspecialchars($t['project_detail_btn'], ENT_QUOTES) ?>
                        </a>
                        <a href="/operationnel?projet_id=<?= (int)$p['id'] ?>"
                           class="btn btn-sm btn-outline-secondary rounded-3"
                           title="Opérationnel">
                            <i class="bi bi-clipboard2-data"></i>
                        </a>
                        <button class="btn btn-sm btn-outline-secondary rounded-3"
                                data-bs-toggle="modal" data-bs-target="#modalEditProject"
                                data-id="<?= (int)$p['id'] ?>"
                                data-nom="<?= htmlspecialchars((string)$p['nom'], ENT_QUOTES) ?>"
                                data-description="<?= htmlspecialchars((string)($p['description'] ?? ''), ENT_QUOTES) ?>"
                                data-statut="<?= htmlspecialchars((string)$p['statut'], ENT_QUOTES) ?>"
                                data-budget="<?= htmlspecialchars((string)($p['budget'] ?? ''), ENT_QUOTES) ?>"
                                data-date_debut="<?= htmlspecialchars((string)($p['date_debut'] ?? ''), ENT_QUOTES) ?>"
                                data-date_fin="<?= htmlspecialchars((string)($p['date_fin'] ?? ''), ENT_QUOTES) ?>">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger rounded-3"
                                data-bs-toggle="modal" data-bs-target="#modalDeleteProject"
                                data-id="<?= (int)$p['id'] ?>"
                                data-nom="<?= htmlspecialchars((string)$p['nom'], ENT_QUOTES) ?>">
                            <i class="bi bi-trash"></i>
                        </button>
                    </footer>
                </article>
            </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<!-- ════ Modale : Nouveau projet ════ -->
<dialog id="modalNewProject" class="modal fade" tabindex="-1" aria-modal="true">
    <section class="modal-dialog modal-dialog-centered">
        <article class="modal-content border-0 shadow-lg rounded-4">
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
                    <div class="mb-3">
                        <label for="np-nom" class="form-label fw-semibold">
                            <?= htmlspecialchars($t['project_field_name'], ENT_QUOTES) ?> <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="np-nom" name="nom" class="form-control rounded-3"
                               placeholder="Ex : Festival Été 2026" required>
                    </div>
                    <div class="mb-3">
                        <label for="np-desc" class="form-label fw-semibold"><?= htmlspecialchars($t['project_field_desc'], ENT_QUOTES) ?></label>
                        <textarea id="np-desc" name="description" class="form-control rounded-3" rows="3"></textarea>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col">
                            <label for="np-statut" class="form-label fw-semibold"><?= htmlspecialchars($t['project_field_status'], ENT_QUOTES) ?></label>
                            <select id="np-statut" name="statut" class="form-select rounded-3">
                                <?php foreach ($statuts as $key => $s): ?>
                                    <option value="<?= $key ?>"><?= htmlspecialchars($s['label'], ENT_QUOTES) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col">
                            <label for="np-budget" class="form-label fw-semibold"><?= htmlspecialchars($t['project_field_budget'], ENT_QUOTES) ?> (€)</label>
                            <input type="number" id="np-budget" name="budget" class="form-control rounded-3" min="0" step="0.01" placeholder="0.00">
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col">
                            <label for="np-debut" class="form-label fw-semibold"><?= htmlspecialchars($t['form_start_date'], ENT_QUOTES) ?></label>
                            <input type="date" id="np-debut" name="date_debut" class="form-control rounded-3">
                        </div>
                        <div class="col">
                            <label for="np-fin" class="form-label fw-semibold"><?= htmlspecialchars($t['form_end_date'], ENT_QUOTES) ?></label>
                            <input type="date" id="np-fin" name="date_fin" class="form-control rounded-3">
                        </div>
                    </div>
                </div>
                <footer class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary fw-bold rounded-3">
                        <i class="bi bi-check-lg me-2"></i><?= htmlspecialchars($t['project_create_btn'], ENT_QUOTES) ?>
                    </button>
                </footer>
            </form>
        </article>
    </section>
</dialog>

<!-- ════ Modale : Modifier projet ════ -->
<dialog id="modalEditProject" class="modal fade" tabindex="-1" aria-modal="true">
    <section class="modal-dialog modal-dialog-centered">
        <article class="modal-content border-0 shadow-lg rounded-4">
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
                    <div class="mb-3">
                        <label for="ep-nom" class="form-label fw-semibold"><?= htmlspecialchars($t['project_field_name'], ENT_QUOTES) ?> <span class="text-danger">*</span></label>
                        <input type="text" id="ep-nom" name="nom" class="form-control rounded-3" required>
                    </div>
                    <div class="mb-3">
                        <label for="ep-desc" class="form-label fw-semibold"><?= htmlspecialchars($t['project_field_desc'], ENT_QUOTES) ?></label>
                        <textarea id="ep-desc" name="description" class="form-control rounded-3" rows="3"></textarea>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col">
                            <label for="ep-statut" class="form-label fw-semibold"><?= htmlspecialchars($t['project_field_status'], ENT_QUOTES) ?></label>
                            <select id="ep-statut" name="statut" class="form-select rounded-3">
                                <?php foreach ($statuts as $key => $s): ?>
                                    <option value="<?= $key ?>"><?= htmlspecialchars($s['label'], ENT_QUOTES) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col">
                            <label for="ep-budget" class="form-label fw-semibold"><?= htmlspecialchars($t['project_field_budget'], ENT_QUOTES) ?> (€)</label>
                            <input type="number" id="ep-budget" name="budget" class="form-control rounded-3" min="0" step="0.01">
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col">
                            <label for="ep-debut" class="form-label fw-semibold"><?= htmlspecialchars($t['form_start_date'], ENT_QUOTES) ?></label>
                            <input type="date" id="ep-debut" name="date_debut" class="form-control rounded-3">
                        </div>
                        <div class="col">
                            <label for="ep-fin" class="form-label fw-semibold"><?= htmlspecialchars($t['form_end_date'], ENT_QUOTES) ?></label>
                            <input type="date" id="ep-fin" name="date_fin" class="form-control rounded-3">
                        </div>
                    </div>
                </div>
                <footer class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-warning fw-bold rounded-3">
                        <i class="bi bi-save me-2"></i><?= htmlspecialchars($t['btn_save'], ENT_QUOTES) ?>
                    </button>
                </footer>
            </form>
        </article>
    </section>
</dialog>

<!-- ════ Modale : Supprimer projet ════ -->
<dialog id="modalDeleteProject" class="modal fade" tabindex="-1" aria-modal="true">
    <section class="modal-dialog modal-dialog-centered">
        <article class="modal-content border-0 shadow-lg rounded-4">
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
                    <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">
                        <?= htmlspecialchars($t['btn_cancel'], ENT_QUOTES) ?>
                    </button>
                    <button type="submit" class="btn btn-danger fw-bold rounded-3">
                        <i class="bi bi-trash me-2"></i><?= htmlspecialchars($t['btn_delete'], ENT_QUOTES) ?>
                    </button>
                </footer>
            </form>
        </article>
    </section>
</dialog>