<?php
declare(strict_types=1);

$recettes   = (float) ($projet['total_recettes'] ?? 0);
$depenses   = (float) ($projet['total_depenses'] ?? 0);
$budget     = (float) ($projet['budget']         ?? 0);
$solde      = $recettes - $depenses;
$ecart      = $budget > 0 ? $budget - $depenses : null;

$statuts = [
    'en_cours'   => ['label' => 'En cours',   'color' => 'primary'],
    'en_attente' => ['label' => 'En attente', 'color' => 'warning'],
    'termine'    => ['label' => 'Terminé',    'color' => 'success'],
    'archive'    => ['label' => 'Archivé',    'color' => 'secondary'],
];
$statutInfo = $statuts[$projet['statut']] ?? $statuts['en_cours'];
$categories = ['Subvention','Sponsoring','Billetterie','Hébergement','Transport',
               'Communication','Matériel','Logistique','Staff','Technique','Autre'];
?>
<div class="container-fluid py-4">

    <!-- ── En-tête ───────────────────────────────────────── -->
    <header class="d-flex align-items-center gap-3 mb-4 flex-wrap">
        <a href="/projets" class="btn btn-outline-secondary btn-sm rounded-3">
            <i class="bi bi-arrow-left me-1"></i><?= htmlspecialchars($t['btn_back'], ENT_QUOTES) ?>
        </a>
        <hgroup class="flex-grow-1">
            <h1 class="fw-bold text-body mb-0 fs-4">
                <i class="bi bi-kanban-fill me-2 text-primary"></i>
                <?= htmlspecialchars((string)$projet['nom'], ENT_QUOTES) ?>
            </h1>
            <p class="text-body-secondary mb-0 small">
                <span class="badge bg-<?= $statutInfo['color'] ?>-subtle text-<?= $statutInfo['color'] ?> rounded-pill me-2">
                    <?= htmlspecialchars($statutInfo['label'], ENT_QUOTES) ?>
                </span>
                <?php if (!empty($projet['date_debut'])): ?>
                    <time datetime="<?= htmlspecialchars((string)$projet['date_debut'], ENT_QUOTES) ?>">
                        <?= date('d/m/Y', strtotime((string)$projet['date_debut'])) ?>
                    </time>
                    <?php if (!empty($projet['date_fin'])): ?>
                        → <time><?= date('d/m/Y', strtotime((string)$projet['date_fin'])) ?></time>
                    <?php endif; ?>
                <?php endif; ?>
            </p>
        </hgroup>
        <div class="d-flex gap-2 flex-wrap">
            <!-- Lien vers Opérationnel -->
            <a href="/operationnel?projet_id=<?= (int)$projet['id'] ?>"
               class="btn btn-primary btn-sm fw-semibold rounded-3 shadow-sm">
                <i class="bi bi-clipboard2-data me-1"></i>Opérationnel
            </a>
            <!-- Accès direct budget -->
            <a href="/operationnel?projet_id=<?= (int)$projet['id'] ?>&tab=pane-budget"
               class="btn btn-outline-success btn-sm rounded-3">
                <i class="bi bi-bar-chart-fill me-1"></i>Budget
            </a>
            <!-- Accès direct planning -->
            <a href="/operationnel?projet_id=<?= (int)$projet['id'] ?>&tab=pane-planning"
               class="btn btn-outline-primary btn-sm rounded-3">
                <i class="bi bi-calendar3 me-1"></i>Planning
            </a>
            <button class="btn btn-outline-warning btn-sm rounded-3"
                    data-bs-toggle="modal" data-bs-target="#modalEditProject"
                    data-id="<?= (int)$projet['id'] ?>"
                    data-nom="<?= htmlspecialchars((string)$projet['nom'], ENT_QUOTES) ?>"
                    data-description="<?= htmlspecialchars((string)($projet['description'] ?? ''), ENT_QUOTES) ?>"
                    data-statut="<?= htmlspecialchars((string)$projet['statut'], ENT_QUOTES) ?>"
                    data-budget="<?= htmlspecialchars((string)($projet['budget'] ?? ''), ENT_QUOTES) ?>"
                    data-date_debut="<?= htmlspecialchars((string)($projet['date_debut'] ?? ''), ENT_QUOTES) ?>"
                    data-date_fin="<?= htmlspecialchars((string)($projet['date_fin'] ?? ''), ENT_QUOTES) ?>">
                <i class="bi bi-pencil me-1"></i><?= htmlspecialchars($t['btn_edit'], ENT_QUOTES) ?>
            </button>
        </div>
    </header>

    <?php if ($projetMsg !== null): ?>
        <aside class="alert alert-<?= $projetType === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show shadow-sm mb-4" role="alert">
            <i class="bi bi-<?= $projetType === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill' ?> me-2"></i>
            <?= htmlspecialchars((string)$projetMsg, ENT_QUOTES) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </aside>
    <?php endif; ?>

    <?php if (!empty($projet['description'])): ?>
    <section class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-4">
            <p class="text-body-secondary mb-0"><?= nl2br(htmlspecialchars((string)$projet['description'], ENT_QUOTES)) ?></p>
        </div>
    </section>
    <?php endif; ?>

    <!-- ── KPIs financiers ───────────────────────────────── -->
    <section class="mb-4" aria-label="Résumé financier">
        <ul class="row row-cols-2 row-cols-md-4 g-3 list-unstyled">
            <?php if ($budget > 0): ?>
            <li class="col">
                <article class="card border-0 shadow-sm rounded-3 text-center h-100">
                    <div class="card-body py-3">
                        <i class="bi bi-wallet2 text-primary fs-4 d-block mb-1"></i>
                        <p class="text-body-secondary small mb-1"><?= htmlspecialchars($t['project_budget'], ENT_QUOTES) ?></p>
                        <p class="fw-bold fs-5 mb-0 text-primary"><?= number_format($budget, 2, ',', ' ') ?> €</p>
                    </div>
                </article>
            </li>
            <?php endif; ?>
            <li class="col">
                <article class="card border-0 shadow-sm rounded-3 text-center h-100">
                    <div class="card-body py-3">
                        <i class="bi bi-arrow-up-circle text-success fs-4 d-block mb-1"></i>
                        <p class="text-body-secondary small mb-1"><?= htmlspecialchars($t['project_recettes'], ENT_QUOTES) ?></p>
                        <p class="fw-bold fs-5 mb-0 text-success"><?= number_format($recettes, 2, ',', ' ') ?> €</p>
                    </div>
                </article>
            </li>
            <li class="col">
                <article class="card border-0 shadow-sm rounded-3 text-center h-100">
                    <div class="card-body py-3">
                        <i class="bi bi-arrow-down-circle text-danger fs-4 d-block mb-1"></i>
                        <p class="text-body-secondary small mb-1"><?= htmlspecialchars($t['project_depenses'], ENT_QUOTES) ?></p>
                        <p class="fw-bold fs-5 mb-0 text-danger"><?= number_format($depenses, 2, ',', ' ') ?> €</p>
                    </div>
                </article>
            </li>
            <li class="col">
                <article class="card border-0 shadow-sm rounded-3 text-center h-100">
                    <div class="card-body py-3">
                        <i class="bi bi-calculator text-info fs-4 d-block mb-1"></i>
                        <p class="text-body-secondary small mb-1"><?= htmlspecialchars($t['project_solde'], ENT_QUOTES) ?></p>
                        <p class="fw-bold fs-5 mb-0 <?= $solde >= 0 ? 'text-success' : 'text-danger' ?>">
                            <?= ($solde >= 0 ? '+' : '') . number_format($solde, 2, ',', ' ') ?> €
                        </p>
                    </div>
                </article>
            </li>
            <?php if ($ecart !== null): ?>
            <li class="col">
                <article class="card border-0 shadow-sm rounded-3 text-center h-100">
                    <div class="card-body py-3">
                        <i class="bi bi-graph-up text-<?= $ecart >= 0 ? 'success' : 'danger' ?> fs-4 d-block mb-1"></i>
                        <p class="text-body-secondary small mb-1">Reste du budget</p>
                        <p class="fw-bold fs-5 mb-0 text-<?= $ecart >= 0 ? 'success' : 'danger' ?>">
                            <?= ($ecart >= 0 ? '+' : '') . number_format($ecart, 2, ',', ' ') ?> €
                        </p>
                    </div>
                </article>
            </li>
            <?php endif; ?>
        </ul>
    </section>

    <div class="row g-4">

        <!-- ── Finance ─────────────────────────────────── -->
        <section class="col-12 col-xl-7" aria-labelledby="finance-heading">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-3 px-4">
                    <h2 id="finance-heading" class="fw-bold fs-5 mb-0">
                        <i class="bi bi-cash-coin me-2 text-success"></i>
                        <?= htmlspecialchars($t['project_finance_title'], ENT_QUOTES) ?>
                    </h2>
                    <button class="btn btn-success btn-sm fw-semibold rounded-3"
                            data-bs-toggle="modal" data-bs-target="#modalAddFinance">
                        <i class="bi bi-plus-lg me-1"></i><?= htmlspecialchars($t['project_finance_add_btn'], ENT_QUOTES) ?>
                    </button>
                </div>
                <div class="card-body p-0">
                <?php if (empty($finance)): ?>
                    <p class="text-body-secondary text-center py-4 small mb-0">
                        <i class="bi bi-info-circle me-2"></i><?= htmlspecialchars($t['project_finance_empty'], ENT_QUOTES) ?>
                    </p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small">
                                <tr>
                                    <th class="ps-4"><?= htmlspecialchars($t['project_finance_date'], ENT_QUOTES) ?></th>
                                    <th><?= htmlspecialchars($t['project_finance_type'], ENT_QUOTES) ?></th>
                                    <th><?= htmlspecialchars($t['project_finance_libelle'], ENT_QUOTES) ?></th>
                                    <th><?= htmlspecialchars($t['project_finance_categorie'], ENT_QUOTES) ?></th>
                                    <th class="text-end"><?= htmlspecialchars($t['project_finance_montant'], ENT_QUOTES) ?></th>
                                    <th class="pe-4"></th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($finance as $f): ?>
                            <tr>
                                <td class="ps-4 text-body-secondary small">
                                    <time datetime="<?= htmlspecialchars((string)$f['date_operation'], ENT_QUOTES) ?>">
                                        <?= date('d/m/Y', strtotime((string)$f['date_operation'])) ?>
                                    </time>
                                </td>
                                <td>
                                    <span class="badge rounded-pill bg-<?= $f['type'] === 'recette' ? 'success' : 'danger' ?>-subtle text-<?= $f['type'] === 'recette' ? 'success' : 'danger' ?>">
                                        <?= $f['type'] === 'recette' ? '↑ Recette' : '↓ Dépense' ?>
                                    </span>
                                </td>
                                <td class="fw-medium small"><?= htmlspecialchars((string)$f['libelle'], ENT_QUOTES) ?></td>
                                <td class="text-body-secondary small"><?= htmlspecialchars((string)($f['categorie'] ?? '—'), ENT_QUOTES) ?></td>
                                <td class="text-end fw-bold <?= $f['type'] === 'recette' ? 'text-success' : 'text-danger' ?>">
                                    <?= $f['type'] === 'recette' ? '+' : '-' ?><?= number_format((float)$f['montant'], 2, ',', ' ') ?> €
                                </td>
                                <td class="pe-4 text-end">
                                    <form method="POST" action="" class="d-inline"
                                          onsubmit="return confirm('Supprimer cette ligne ?')">
                                        <input type="hidden" name="project_action" value="delete_finance">
                                        <input type="hidden" name="finance_id" value="<?= (int)$f['id'] ?>">
                                        <input type="hidden" name="projet_id"  value="<?= (int)$projet['id'] ?>">
                                        <button type="submit" class="btn btn-link btn-sm text-danger p-0">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php if (!empty($f['note'])): ?>
                            <tr class="table-secondary">
                                <td colspan="6" class="ps-4 py-1 text-muted small fst-italic">
                                    <i class="bi bi-chat-left-text me-1"></i><?= htmlspecialchars((string)$f['note'], ENT_QUOTES) ?>
                                </td>
                            </tr>
                            <?php endif; ?>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- ── Événements liés ─────────────────────────── -->
        <aside class="col-12 col-xl-5" aria-labelledby="events-heading">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-3 px-4">
                    <h2 id="events-heading" class="fw-bold fs-5 mb-0">
                        <i class="bi bi-calendar-event-fill me-2 text-warning"></i>
                        <?= htmlspecialchars($t['project_events_title'], ENT_QUOTES) ?>
                    </h2>
                    <div class="d-flex gap-2">
                        <!-- Lier un événement existant -->
                        <?php if (!empty($unlinkedEvents)): ?>
                        <button class="btn btn-outline-warning btn-sm rounded-3"
                                data-bs-toggle="modal" data-bs-target="#modalLinkEvent">
                            <i class="bi bi-link-45deg me-1"></i>Lier existant
                        </button>
                        <?php endif; ?>
                        <!-- Créer un nouvel événement lié -->
                        <a href="/nouvel_event?projet_id=<?= (int)$projet['id'] ?>"
                           class="btn btn-warning btn-sm fw-semibold rounded-3">
                            <i class="bi bi-plus-lg me-1"></i>Nouvel événement
                        </a>
                    </div>
                </div>
                <div class="card-body p-3">
                <?php if (empty($evenements)): ?>
                    <p class="text-body-secondary text-center py-4 small mb-0">
                        <i class="bi bi-calendar-x fs-2 d-block mb-2 opacity-50"></i>
                        <?= htmlspecialchars($t['project_events_empty'], ENT_QUOTES) ?>
                    </p>
                <?php else: ?>
                    <ul class="list-unstyled mb-0">
                    <?php foreach ($evenements as $ev): ?>
                    <li class="mb-3">
                        <article class="card border-0 bg-body-secondary rounded-3">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <h3 class="fw-bold mb-0 fs-6"><?= htmlspecialchars((string)$ev['nom'], ENT_QUOTES) ?></h3>
                                    <?php if (!empty($ev['sport'])): ?>
                                    <span class="badge bg-primary-subtle text-primary rounded-pill small">
                                        <?= htmlspecialchars((string)$ev['sport'], ENT_QUOTES) ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                                <ul class="list-unstyled small text-body-secondary mb-2">
                                    <li>
                                        <i class="bi bi-calendar me-1 text-primary"></i>
                                        <time><?= date('d/m/Y', strtotime((string)$ev['date_debut'])) ?></time>
                                        <?php if (!empty($ev['date_fin'])): ?>
                                        → <time><?= date('d/m/Y', strtotime((string)$ev['date_fin'])) ?></time>
                                        <?php endif; ?>
                                    </li>
                                    <?php if (!empty($ev['lieu'])): ?>
                                    <li><i class="bi bi-geo-alt me-1 text-danger"></i><?= htmlspecialchars((string)$ev['lieu'], ENT_QUOTES) ?></li>
                                    <?php endif; ?>
                                </ul>
                                <div class="d-flex gap-2">
                                    <a href="/operationnel?event_id=<?= (int)$ev['id'] ?>"
                                       class="btn btn-sm btn-outline-primary rounded-3 flex-grow-1">
                                        <i class="bi bi-clipboard2-data me-1"></i>Opérationnel
                                    </a>
                                    <a href="/gerer_event?id=<?= (int)$ev['id'] ?>"
                                       class="btn btn-sm btn-outline-secondary rounded-3">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" action="" class="d-inline"
                                          onsubmit="return confirm('Détacher cet événement du projet ?')">
                                        <input type="hidden" name="project_action" value="detach_event">
                                        <input type="hidden" name="event_id"       value="<?= (int)$ev['id'] ?>">
                                        <input type="hidden" name="projet_id"      value="<?= (int)$projet['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-3"
                                                title="Détacher du projet">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    </li>
                    <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                </div>
            </div>
        </aside>
    </div>

    <!-- ── Contacts liés au projet ─────────────────────── -->
    <section class="card border-0 shadow-sm rounded-3 mt-4">
        <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-3 px-4">
            <h2 class="fw-bold fs-5 mb-0">
                <i class="bi bi-person-lines-fill me-2 text-secondary"></i>
                Contacts liés
            </h2>
            <a href="/annuaire" class="btn btn-sm btn-outline-secondary rounded-3">
                <i class="bi bi-person-plus me-1"></i>Gérer dans l'annuaire
            </a>
        </div>
        <div class="card-body p-3">
        <?php
        $contactsProjet = [];
        try { $contactsProjet = (new \App\Models\ContactModel())->getByProjet((int)$projet['id']); } catch (\Exception) {}
        ?>
        <?php if (empty($contactsProjet)): ?>
            <p class="text-body-secondary small text-center py-3 mb-0">
                <i class="bi bi-info-circle me-1"></i>
                Aucun contact lié. Allez dans l'<a href="/annuaire">Annuaire</a> et utilisez le bouton
                <strong><i class="bi bi-link-45deg"></i></strong> sur un contact pour le lier à ce projet.
            </p>
        <?php else: ?>
            <ul class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3 list-unstyled mb-0">
                <?php foreach ($contactsProjet as $cl): ?>
                <li class="col">
                    <div class="d-flex align-items-center gap-3 p-2 rounded-3 bg-body-secondary">
                        <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                             style="width:36px;height:36px;font-size:.85rem;">
                            <?= mb_strtoupper(mb_substr($cl['nom'], 0, 1)) ?>
                        </div>
                        <div class="flex-grow-1 min-width-0">
                            <p class="fw-semibold mb-0 small text-truncate"><?= htmlspecialchars((string)$cl['nom'], ENT_QUOTES) ?></p>
                            <?php if (!empty($cl['lien_role'])): ?>
                            <span class="badge bg-info-subtle text-info rounded-pill" style="font-size:.7rem;">
                                <?= htmlspecialchars((string)$cl['lien_role'], ENT_QUOTES) ?>
                            </span>
                            <?php endif; ?>
                            <?php if (!empty($cl['telephone'])): ?>
                            <p class="text-body-secondary mb-0" style="font-size:.75rem;">
                                <a href="tel:<?= htmlspecialchars($cl['telephone'], ENT_QUOTES) ?>" class="text-decoration-none">
                                    <?= htmlspecialchars($cl['telephone'], ENT_QUOTES) ?>
                                </a>
                            </p>
                            <?php endif; ?>
                        </div>
                        <form method="POST" action="" class="d-inline flex-shrink-0"
                              onsubmit="return confirm('Détacher ce contact ?')">
                            <input type="hidden" name="project_action" value="detach_contact">
                            <input type="hidden" name="lien_id" value="<?= (int)$cl['lien_id'] ?>">
                            <input type="hidden" name="projet_id" value="<?= (int)$projet['id'] ?>">
                            <button class="btn btn-sm btn-outline-danger rounded-3 py-0 px-2">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </form>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        </div>
    </section>

    <!-- ── Gantt des événements ───────────────────────── -->
    <?php if (!empty($ganttEvents)): ?>
    <section class="card border-0 shadow-sm rounded-3 mt-4">
        <div class="card-header bg-transparent border-0 pt-3 px-4">
            <h2 class="fw-bold fs-5 mb-0">
                <i class="bi bi-bar-chart-steps me-2 text-info"></i>Planning des événements
            </h2>
        </div>
        <div class="card-body p-3">
            <div id="projet-gantt-container" style="overflow:auto;min-height:120px;"></div>
        </div>
    </section>
    <?php endif; ?>

</div><!-- /.container-fluid -->


<!-- ════════════════════════════════════════════════════════
     MODALS
════════════════════════════════════════════════════════ -->

<!-- Lier un événement existant -->
<?php if (!empty($unlinkedEvents)): ?>
<div class="modal fade" id="modalLinkEvent" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-link-45deg me-2 text-warning"></i>Lier un événement existant
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="">
                    <input type="hidden" name="project_action" value="attach_event">
                    <input type="hidden" name="projet_id"      value="<?= (int)$projet['id'] ?>">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Choisir un événement</label>
                        <select name="event_id" class="form-select rounded-3" required>
                            <option value="">— Sélectionner —</option>
                            <?php foreach ($unlinkedEvents as $ev): ?>
                            <option value="<?= (int)$ev['id'] ?>">
                                <?= htmlspecialchars((string)$ev['nom'], ENT_QUOTES) ?>
                                <?= !empty($ev['date_debut']) ? ' — ' . date('d/m/Y', strtotime($ev['date_debut'])) : '' ?>
                                <?= !empty($ev['lieu']) ? ' · ' . htmlspecialchars($ev['lieu'], ENT_QUOTES) : '' ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text text-body-secondary">
                            Seuls les événements non encore liés à un projet sont affichés.
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-warning rounded-3 fw-semibold">
                            <i class="bi bi-link-45deg me-1"></i>Lier l'événement
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Ajouter ligne financière -->
<dialog id="modalAddFinance" class="modal fade" tabindex="-1" aria-modal="true">
    <section class="modal-dialog">
        <article class="modal-content border-0 shadow-lg rounded-4">
            <form method="POST" action="">
                <input type="hidden" name="project_action" value="add_finance">
                <input type="hidden" name="projet_id" value="<?= (int)$projet['id'] ?>">
                <header class="modal-header border-0 pb-0">
                    <h2 class="modal-title fw-bold fs-5">
                        <i class="bi bi-plus-circle-fill me-2 text-success"></i>
                        <?= htmlspecialchars($t['project_finance_modal_title'], ENT_QUOTES) ?>
                    </h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </header>
                <div class="modal-body">
                    <section class="row mb-3">
                        <p class="col mb-0">
                            <label for="af-type" class="form-label small fw-bold"><?= htmlspecialchars($t['project_finance_type'], ENT_QUOTES) ?> <abbr title="obligatoire" class="text-danger">*</abbr></label>
                            <select id="af-type" name="type" class="form-select" required>
                                <option value="recette">✅ Recette</option>
                                <option value="depense">🔴 Dépense</option>
                            </select>
                        </p>
                        <p class="col mb-0">
                            <label for="af-montant" class="form-label small fw-bold"><?= htmlspecialchars($t['project_finance_montant'], ENT_QUOTES) ?> (€) <abbr title="obligatoire" class="text-danger">*</abbr></label>
                            <input type="number" id="af-montant" name="montant" class="form-control" min="0.01" step="0.01" placeholder="0.00" required>
                        </p>
                    </section>
                    <p class="mb-3">
                        <label for="af-libelle" class="form-label small fw-bold"><?= htmlspecialchars($t['project_finance_libelle'], ENT_QUOTES) ?> <abbr title="obligatoire" class="text-danger">*</abbr></label>
                        <input type="text" id="af-libelle" name="libelle" class="form-control" placeholder="Ex : Sponsoring SAEM..." required>
                    </p>
                    <section class="row mb-3">
                        <p class="col mb-0">
                            <label for="af-categorie" class="form-label small fw-bold"><?= htmlspecialchars($t['project_finance_categorie'], ENT_QUOTES) ?></label>
                            <select id="af-categorie" name="categorie" class="form-select">
                                <option value="">— Aucune —</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= htmlspecialchars($cat, ENT_QUOTES) ?>"><?= htmlspecialchars($cat, ENT_QUOTES) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </p>
                        <p class="col mb-0">
                            <label for="af-date" class="form-label small fw-bold"><?= htmlspecialchars($t['project_finance_date'], ENT_QUOTES) ?></label>
                            <input type="date" id="af-date" name="date_operation" class="form-control" value="<?= date('Y-m-d') ?>">
                        </p>
                    </section>
                    <p class="mb-0">
                        <label for="af-note" class="form-label small fw-bold"><?= htmlspecialchars($t['project_finance_note'], ENT_QUOTES) ?></label>
                        <textarea id="af-note" name="note" class="form-control" rows="2" placeholder="Remarque, prestataire..."></textarea>
                    </p>
                </div>
                <footer class="modal-footer border-0 pt-0">
                    <button type="submit" class="btn btn-success w-100 fw-bold">
                        <i class="bi bi-check-lg me-2"></i><?= htmlspecialchars($t['project_finance_save_btn'], ENT_QUOTES) ?>
                    </button>
                </footer>
            </form>
        </article>
    </section>
</dialog>

<!-- Modifier projet -->
<dialog id="modalEditProject" class="modal fade" tabindex="-1" aria-modal="true">
    <section class="modal-dialog">
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

<!-- Données Gantt pour le JS -->
<script>
window.PROJET_GANTT_DATA = <?= json_encode($ganttEvents, JSON_HEX_TAG) ?>;
window.PROJET_ID = <?= (int)$projet['id'] ?>;
</script>