<?php

/**
 * YES – Your Event Solution
 * @file dashboard.php
 * @version 2.2  –  2026
 *
 * Structure :
 *   - KPIs (événements / todos)
 *   - Grille principale : événements (table) + todolist côte à côte
 *   - Planning global (Gantt/liste) en dessous
 */

declare(strict_types=1);

$statuts_colors = [
    'wip'      => 'warning',
    'en_cours' => 'primary',
    'valide'   => 'success',
    'annule'   => 'danger',
];
$statuts_labels = [
    'wip'      => 'WIP',
    'en_cours' => 'En cours',
    'valide'   => 'Validé',
    'annule'   => 'Annulé',
];

$pgWithDates = array_values(array_filter($planningGlobal ?? [], fn($p) => !empty($p['date_debut'])));

$now           = time();
$upcomingCount = 0;
foreach ($evenements as $ev) {
    if (!empty($ev['date_debut']) && strtotime($ev['date_debut']) >= $now) $upcomingCount++;
}
$totalEvents = count($evenements);
?>

<div class="container-fluid py-4">

    <!-- ── Flash messages ─────────────────────────────────── -->
    <?php if ($todoMsg !== null): ?>
        <aside class="alert alert-<?= $todoType === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show shadow-sm mb-4" role="alert">
            <i class="bi bi-<?= $todoType === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill' ?> me-2"></i>
            <?= htmlspecialchars((string)$todoMsg, ENT_QUOTES) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </aside>
    <?php endif; ?>

    <?php if (isset($_SESSION['success_msg'])): ?>
        <aside class="alert alert-success alert-dismissible fade show shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?= htmlspecialchars((string)$_SESSION['success_msg'], ENT_QUOTES) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </aside>
        <?php unset($_SESSION['success_msg']); ?>
    <?php endif; ?>

    <?php if (!empty($erreur_bdd)): ?>
        <aside class="alert alert-warning border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= htmlspecialchars((string)$erreur_bdd, ENT_QUOTES) ?>
        </aside>
    <?php endif; ?>

    <!-- ── KPIs ──────────────────────────────────────────── -->
    <ul class="row row-cols-2 row-cols-md-4 g-3 list-unstyled mb-4">
        <?php foreach ([
            ['label' => 'Événements totaux',  'val' => $totalEvents,           'color' => 'primary', 'icon' => 'bi-calendar-event-fill'],
            ['label' => 'À venir',             'val' => $upcomingCount,         'color' => 'success', 'icon' => 'bi-calendar-check-fill'],
            ['label' => 'Tâches en cours',     'val' => $todoStats['en_cours'], 'color' => 'warning', 'icon' => 'bi-hourglass-split'],
            ['label' => 'Tâches terminées',    'val' => $todoStats['done'],     'color' => 'info',    'icon' => 'bi-check2-all'],
        ] as $kpi): ?>
        <li class="col">
            <article class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi <?= $kpi['icon'] ?> text-<?= $kpi['color'] ?> fs-3 d-block mb-1"></i>
                    <p class="text-body-secondary small mb-1"><?= $kpi['label'] ?></p>
                    <p class="fw-bold fs-4 mb-0 text-<?= $kpi['color'] ?>"><?= (int)$kpi['val'] ?></p>
                </div>
            </article>
        </li>
        <?php endforeach; ?>
    </ul>

    <!-- ── Grille principale : Événements | Todolist ──────── -->
    <div class="row g-4 mb-4">

        <!-- Colonne gauche : Événements -->
        <div class="col-lg-7">
            <section class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-3 pb-0 px-4">
                    <h2 class="fw-bold fs-5 mb-0">
                        <i class="bi bi-calendar-event me-2 text-primary"></i>
                        <?= htmlspecialchars($t['dash_events_title'], ENT_QUOTES) ?>
                    </h2>
                    <a href="/nouvel_event" class="btn btn-primary btn-sm fw-semibold shadow-sm rounded-3"
                       <?= !($canManageEvents ?? false) ? 'style="display:none"' : '' ?>>
                        <i class="bi bi-plus-lg me-1"></i>
                        <?= htmlspecialchars($t['nav_new_event'], ENT_QUOTES) ?>
                    </a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($evenements)): ?>
                        <p class="text-body-secondary text-center py-5 mb-0">
                            <i class="bi bi-calendar-x fs-2 d-block mb-2 opacity-50"></i>
                            <?= htmlspecialchars($t['dash_empty_desc'], ENT_QUOTES) ?>
                        </p>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light small">
                                <tr>
                                    <th class="ps-4 fw-semibold">Événement</th>
                                    <th class="fw-semibold">Sport</th>
                                    <th class="fw-semibold">Lieu</th>
                                    <th class="fw-semibold">Date</th>
                                    <th class="pe-3"></th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($evenements as $ev): ?>
                            <tr>
                                <td class="ps-4 fw-semibold"><?= htmlspecialchars((string)$ev['nom'], ENT_QUOTES) ?></td>
                                <td class="small">
                                    <?php if (!empty($ev['sport'])): ?>
                                    <span class="badge bg-primary-subtle text-primary rounded-pill">
                                        <?= htmlspecialchars((string)$ev['sport'], ENT_QUOTES) ?>
                                    </span>
                                    <?php else: ?>
                                    <span class="text-body-secondary">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="small text-body-secondary">
                                    <?= !empty($ev['lieu']) ? htmlspecialchars((string)$ev['lieu'], ENT_QUOTES) : '—' ?>
                                </td>
                                <td class="small">
                                    <?php if (!empty($ev['date_debut'])): ?>
                                    <span class="<?= strtotime($ev['date_debut']) >= $now ? 'text-success fw-semibold' : 'text-body-secondary' ?>">
                                        <?= date('d/m/Y', strtotime((string)$ev['date_debut'])) ?>
                                    </span>
                                    <?php if (!empty($ev['date_fin'])): ?>
                                    <span class="text-body-secondary"> → <?= date('d/m/Y', strtotime((string)$ev['date_fin'])) ?></span>
                                    <?php endif; ?>
                                    <?php else: ?>—<?php endif; ?>
                                </td>
                                <td class="pe-3 text-end">
                                    <a href="/operationnel?event_id=<?= (int)$ev['id'] ?>"
                                       class="btn btn-sm btn-outline-primary rounded-3 me-1 py-0 px-2"
                                       title="Opérationnel">
                                        <i class="bi bi-clipboard2-data"></i>
                                    </a>
                                    <a href="/gerer_event?id=<?= (int)$ev['id'] ?>"
                                       class="btn btn-sm btn-outline-secondary rounded-3 py-0 px-2"
                                       title="<?= htmlspecialchars($t['dash_manage_btn'], ENT_QUOTES) ?>">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button class="btn btn-sm btn-outline-info rounded-3 py-0 px-2"
                                            onclick="openDuplicateModal(<?= (int)$ev['id'] ?>, '<?= htmlspecialchars((string)$ev['nom'], ENT_QUOTES) ?>')"
                                            title="Dupliquer">
                                        <i class="bi bi-copy"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        <!-- Colonne droite : Todolist -->
        <div class="col-lg-5">
            <?php include __DIR__ . '/todolist.php'; ?>
        </div>

    </div>

    <!-- ── Planning global ────────────────────────────────── -->
    <section class="card border-0 shadow-sm rounded-3" aria-labelledby="pg-heading">
        <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-3 pb-0 px-4">
            <h2 id="pg-heading" class="fw-bold fs-5 mb-0">
                <i class="bi bi-bar-chart-steps me-2 text-info"></i>Planning global
            </h2>
            <div class="d-flex gap-2">
                <div class="d-flex gap-1">
                    <button class="btn btn-sm btn-outline-secondary active" id="pg-btn-list"
                            onclick="switchPgView('list')" title="Vue liste">
                        <i class="bi bi-list-ul"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" id="pg-btn-calendar"
                            onclick="switchPgView('calendar')" title="Calendrier">
                        <i class="bi bi-calendar3"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" id="pg-btn-gantt"
                            onclick="switchPgView('gantt')" title="Vue Gantt">
                        <i class="bi bi-bar-chart-steps"></i>
                    </button>
                </div>
                <?php if ($canPlanningGlobal ?? false): ?>
                <button class="btn btn-info btn-sm fw-semibold text-white shadow-sm rounded-3"
                        data-bs-toggle="modal" data-bs-target="#modalPgCreate">
                    <i class="bi bi-plus-lg me-1"></i>Ajouter
                </button>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body p-0">

            <!-- Vue liste -->
            <div id="pg-list-view" class="p-3">
            <?php if (empty($planningGlobal)): ?>
                <p class="text-body-secondary text-center py-4 mb-0">
                    <i class="bi bi-calendar3 fs-2 d-block mb-2 opacity-50"></i>
                    Aucune entrée dans le planning global.
                </p>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light small">
                        <tr>
                            <th class="ps-3">Titre</th>
                            <th>Statut</th>
                            <th>Lié à</th>
                            <th>Début</th>
                            <th>Fin</th>
                            <th class="pe-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($planningGlobal as $pg):
                        $pgColor = $statuts_colors[$pg['statut'] ?? 'wip'] ?? 'secondary';
                        $pgLabel = $statuts_labels[$pg['statut'] ?? 'wip'] ?? $pg['statut'];
                    ?>
                    <tr>
                        <td class="ps-3">
                            <span class="d-inline-block rounded-circle me-2 align-middle flex-shrink-0"
                                  style="width:10px;height:10px;background:<?= htmlspecialchars($pg['couleur'] ?? '#0d6efd', ENT_QUOTES) ?>"></span>
                            <span class="fw-semibold"><?= htmlspecialchars((string)$pg['titre'], ENT_QUOTES) ?></span>
                            <?php if (!empty($pg['description'])): ?>
                            <br><small class="text-body-secondary ms-4"><?= htmlspecialchars((string)$pg['description'], ENT_QUOTES) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-<?= $pgColor ?> rounded-pill">
                                <?= htmlspecialchars($pgLabel, ENT_QUOTES) ?>
                            </span>
                        </td>
                        <td class="small text-body-secondary">
                            <?php if (!empty($pg['event_nom'])): ?>
                                <i class="bi bi-calendar-event me-1 text-primary"></i><?= htmlspecialchars($pg['event_nom'], ENT_QUOTES) ?>
                            <?php elseif (!empty($pg['projet_nom'])): ?>
                                <i class="bi bi-folder me-1 text-warning"></i><?= htmlspecialchars($pg['projet_nom'], ENT_QUOTES) ?>
                            <?php else: ?>—<?php endif; ?>
                        </td>
                        <td class="small"><?= !empty($pg['date_debut']) ? date('d/m/Y', strtotime($pg['date_debut'])) : '—' ?></td>
                        <td class="small"><?= !empty($pg['date_fin'])   ? date('d/m/Y', strtotime($pg['date_fin']))   : '—' ?></td>
                        <td class="pe-3 text-end">
                            <button class="btn btn-sm btn-outline-secondary rounded-3 py-0 px-2 me-1"
                                    onclick="openPgEdit(<?= htmlspecialchars(json_encode($pg), ENT_QUOTES) ?>)">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Supprimer ?')">
                                <input type="hidden" name="pg_action" value="pg_delete">
                                <input type="hidden" name="pg_id"     value="<?= (int)$pg['id'] ?>">
                                <button class="btn btn-sm btn-outline-danger rounded-3 py-0 px-2">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
            </div>

            <!-- Vue Calendrier -->
            <div id="pg-calendar-view" style="display:none;" class="p-3">
                <div class="card border-0 rounded-3">
                    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center py-2 px-3">
                        <button class="btn btn-sm btn-outline-secondary rounded-3" onclick="pgCalNav(-1)">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <span class="fw-bold fs-6" id="pg-cal-title">—</span>
                        <button class="btn btn-sm btn-outline-secondary rounded-3" onclick="pgCalNav(+1)">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                    <div class="card-body p-2">
                        <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:2px;margin-bottom:4px;">
                            <div class="text-center fw-bold small text-body-secondary py-1">Lun</div>
                            <div class="text-center fw-bold small text-body-secondary py-1">Mar</div>
                            <div class="text-center fw-bold small text-body-secondary py-1">Mer</div>
                            <div class="text-center fw-bold small text-body-secondary py-1">Jeu</div>
                            <div class="text-center fw-bold small text-body-secondary py-1">Ven</div>
                            <div class="text-center fw-bold small text-body-secondary py-1">Sam</div>
                            <div class="text-center fw-bold small text-body-secondary py-1">Dim</div>
                        </div>
                        <div id="pg-cal-grid" style="display:grid;grid-template-columns:repeat(7,1fr);gap:4px;min-height:320px;"></div>
                    </div>
                </div>
                <div id="pg-cal-detail" class="mt-3" style="display:none;">
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-header bg-primary-subtle border-0 fw-bold rounded-top-3 small" id="pg-cal-detail-title"></div>
                        <ul class="list-group list-group-flush rounded-bottom-3" id="pg-cal-detail-list"></ul>
                    </div>
                </div>
            </div>

            <!-- Vue Gantt -->
            <div id="pg-gantt-view" style="display:none;" class="p-3">
                <?php if (empty($pgWithDates)): ?>
                <div class="alert alert-info border-0 mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    Ajoutez des dates de début/fin pour afficher le Gantt.
                </div>
                <?php else: ?>
                <div id="pg-gantt-container" style="min-height:200px;overflow:auto;"></div>
                <?php endif; ?>
            </div>

        </div>
    </section>

</div><!-- /.container-fluid -->


<!-- ════════════════════════════════════════════════════════
     MODALS PLANNING GLOBAL
════════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalPgCreate" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold"><i class="bi bi-calendar-plus me-2 text-info"></i>Nouvelle entrée planning global</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form method="POST">
          <input type="hidden" name="pg_action" value="pg_create">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label fw-semibold">Titre <span class="text-danger">*</span></label>
              <input type="text" name="pg_titre" class="form-control rounded-3" required>
            </div>
            <div class="col-md-8">
              <label class="form-label fw-semibold">Statut</label>
              <select name="pg_statut" class="form-select rounded-3">
                <option value="wip">WIP</option>
                <option value="en_cours">En cours</option>
                <option value="valide">Validé</option>
                <option value="annule">Annulé</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Couleur</label>
              <input type="color" name="pg_couleur" class="form-control form-control-color w-100 rounded-3" value="#0d6efd">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Date début <span class="text-danger">*</span></label>
              <input type="date" name="pg_date_debut" class="form-control rounded-3" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Date fin <span class="text-danger">*</span></label>
              <input type="date" name="pg_date_fin" class="form-control rounded-3" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Événement lié</label>
              <select name="pg_event_id" class="form-select rounded-3">
                <option value="">— Aucun —</option>
                <?php foreach ($evenements as $ev): ?>
                <option value="<?= (int)$ev['id'] ?>"><?= htmlspecialchars((string)$ev['nom'], ENT_QUOTES) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Projet lié</label>
              <select name="pg_projet_id" class="form-select rounded-3">
                <option value="">— Aucun —</option>
                <?php foreach ($projets as $pr): ?>
                <option value="<?= (int)$pr['id'] ?>"><?= htmlspecialchars((string)$pr['nom'], ENT_QUOTES) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Description</label>
              <textarea name="pg_description" class="form-control rounded-3" rows="2"></textarea>
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Annuler</button>
            <button type="submit" class="btn btn-info rounded-3 fw-semibold text-white"><i class="bi bi-plus-lg me-1"></i>Ajouter</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalPgEdit" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold"><i class="bi bi-pencil me-2 text-primary"></i>Modifier l'entrée planning global</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form method="POST">
          <input type="hidden" name="pg_action" value="pg_update">
          <input type="hidden" name="pg_id" id="pge-id">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label fw-semibold">Titre</label>
              <input type="text" name="pg_titre" id="pge-titre" class="form-control rounded-3" required>
            </div>
            <div class="col-md-8">
              <label class="form-label fw-semibold">Statut</label>
              <select name="pg_statut" id="pge-statut" class="form-select rounded-3">
                <option value="wip">WIP</option>
                <option value="en_cours">En cours</option>
                <option value="valide">Validé</option>
                <option value="annule">Annulé</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Couleur</label>
              <input type="color" name="pg_couleur" id="pge-couleur" class="form-control form-control-color w-100 rounded-3">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Date début</label>
              <input type="date" name="pg_date_debut" id="pge-debut" class="form-control rounded-3">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Date fin</label>
              <input type="date" name="pg_date_fin" id="pge-fin" class="form-control rounded-3">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Événement lié</label>
              <select name="pg_event_id" id="pge-event" class="form-select rounded-3">
                <option value="">— Aucun —</option>
                <?php foreach ($evenements as $ev): ?>
                <option value="<?= (int)$ev['id'] ?>"><?= htmlspecialchars((string)$ev['nom'], ENT_QUOTES) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Projet lié</label>
              <select name="pg_projet_id" id="pge-projet" class="form-select rounded-3">
                <option value="">— Aucun —</option>
                <?php foreach ($projets as $pr): ?>
                <option value="<?= (int)$pr['id'] ?>"><?= htmlspecialchars((string)$pr['nom'], ENT_QUOTES) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Description</label>
              <textarea name="pg_description" id="pge-desc" class="form-control rounded-3" rows="2"></textarea>
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Annuler</button>
            <button type="submit" class="btn btn-primary rounded-3 fw-semibold"><i class="bi bi-check-lg me-1"></i>Enregistrer</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- ════ Modal : Dupliquer un événement ════ -->
<div class="modal fade" id="modalDuplicateEvent" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold">
          <i class="bi bi-copy me-2 text-info"></i>Dupliquer l'événement
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form method="POST" action="/duplicate_event">
          <input type="hidden" name="source_id" id="dup-source-id">
          <p class="text-body-secondary small mb-3">
            Copie de : <strong id="dup-source-nom"></strong>
          </p>
          <div class="mb-4">
            <label for="dup-nouveau-nom" class="form-label fw-semibold">
              Nom du nouvel événement <span class="text-danger">*</span>
            </label>
            <input type="text" id="dup-nouveau-nom" name="nouveau_nom"
                   class="form-control rounded-3" required
                   placeholder="Ex : Festival Été 2027">
            <div class="form-text text-body-secondary">
              Toutes les informations (lieu, dates, phases, liens) seront copiées.
              Le budget, planning et matériel ne sont pas copiés.
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Annuler</button>
            <button type="submit" class="btn btn-info text-white fw-bold rounded-3">
              <i class="bi bi-copy me-1"></i>Dupliquer
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
window.openDuplicateModal = function(id, nom) {
  document.getElementById('dup-source-id').value = id;
  document.getElementById('dup-source-nom').textContent = nom;
  document.getElementById('dup-nouveau-nom').value = nom + ' (copie)';
  bootstrap.Modal.getOrCreateInstance(document.getElementById('modalDuplicateEvent')).show();
};
</script>

<script>
window.PG_DATA = <?= json_encode($pgWithDates, JSON_HEX_TAG) ?>;
</script>