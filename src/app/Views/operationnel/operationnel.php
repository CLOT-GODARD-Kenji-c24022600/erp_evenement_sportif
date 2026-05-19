<?php

/**
 * YES – Your Event Solution
 * Vue : Opérationnel Événement — Budget / Planning / Matériel / Facturation
 *
 * @file operationnel.php
 * @version 1.1  –  2026
 */

declare(strict_types=1);

// ── Helpers de la vue ───────────────────────────────────────
// On génère les champs cachés pour le contexte et pour le JS
function opsForm(int $eventId, int $projetId): string {
    return '<input type="hidden" name="event_id" value="' . $eventId . '">' .
           '<input type="hidden" name="projet_id" value="' . $projetId . '">' .
           '<input type="hidden" name="active_tab" class="js-active-tab" value="">';
}

$planningStatuts = [
    'wip'      => ['label' => 'WIP',      'color' => 'warning'],
    'en_cours' => ['label' => 'En cours', 'color' => 'primary'],
    'valide'   => ['label' => 'Validé',   'color' => 'success'],
    'maj'      => ['label' => 'Maj',      'color' => 'info'],
    'devis'    => ['label' => 'Devis',    'color' => 'secondary'],
    'visuels'  => ['label' => 'Visuels',  'color' => 'secondary'],
    'bat'      => ['label' => 'BAT',      'color' => 'secondary'],
    'prod'     => ['label' => 'Prod',     'color' => 'secondary'],
    'annule'   => ['label' => 'Annulé',   'color' => 'danger'],
];

$contextLabel = '';
if ($eventId > 0) {
    foreach ($evenements as $ev) {
        if ((int)$ev['id'] === $eventId) { $contextLabel = $ev['nom']; break; }
    }
} elseif ($projetId > 0) {
    foreach ($projets as $pr) {
        if ((int)$pr['id'] === $projetId) { $contextLabel = $pr['nom']; break; }
    }
}

$budgetProduits = array_filter($budget, fn($l) => $l['type'] === 'produit');
$budgetCharges  = array_filter($budget, fn($l) => $l['type'] === 'charge');

$totalFacturation = array_sum(array_map(fn($f) => (float)$f['prix_unitaire'] * (float)$f['quantite'], $facturation));
?>

<div class="container-fluid py-4">

    <!-- ── En-tête + sélecteur de contexte ──────────────────── -->
    <header class="d-flex flex-wrap align-items-center gap-3 mb-4">
        <hgroup class="flex-grow-1">
            <h1 class="fw-bold fs-4 mb-0">
                <i class="bi bi-clipboard2-data-fill me-2 text-primary"></i>
                Opérationnel
                <?php if ($contextLabel): ?>
                    <span class="text-primary">— <?= htmlspecialchars($contextLabel, ENT_QUOTES) ?></span>
                <?php endif; ?>
            </h1>
            <p class="text-body-secondary small mb-0">Budget · Planning · Matériel · Facturation</p>
        </hgroup>

        <!-- Sélecteur événement / projet -->
        <form method="GET" action="/operationnel" class="d-flex gap-2 flex-wrap align-items-center">
            <select name="event_id" class="form-select form-select-sm rounded-3" style="max-width:220px;"
                    onchange="this.form.submit()" aria-label="Choisir un événement">
                <option value="0">— Événement —</option>
                <?php foreach ($evenements as $ev): ?>
                <option value="<?= (int)$ev['id'] ?>" <?= (int)$ev['id'] === $eventId ? 'selected' : '' ?>>
                    <?= htmlspecialchars((string)$ev['nom'], ENT_QUOTES) ?>
                </option>
                <?php endforeach; ?>
            </select>
            <span class="text-body-secondary small">ou</span>
            <select name="projet_id" class="form-select form-select-sm rounded-3" style="max-width:220px;"
                    onchange="this.form.submit()" aria-label="Choisir un projet">
                <option value="0">— Projet —</option>
                <?php foreach ($projets as $pr): ?>
                <option value="<?= (int)$pr['id'] ?>" <?= (int)$pr['id'] === $projetId ? 'selected' : '' ?>>
                    <?= htmlspecialchars((string)$pr['nom'], ENT_QUOTES) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </form>
    </header>

    <?php if ($opsMsg): ?>
    <aside class="alert alert-<?= $opsType === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show shadow-sm mb-4" role="alert">
        <i class="bi bi-<?= $opsType === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill' ?> me-2"></i>
        <?= htmlspecialchars((string)$opsMsg, ENT_QUOTES) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </aside>
    <?php endif; ?>

    <?php if ($eventId <= 0 && $projetId <= 0): ?>
    <div class="text-center py-5 text-body-secondary">
        <i class="bi bi-arrow-up-circle fs-1 d-block mb-3 opacity-50"></i>
        <p class="fs-5">Sélectionne un événement ou un projet pour commencer.</p>
    </div>
    <?php else: ?>

    <!-- ── Onglets ──────────────────────────────────────────── -->
    <ul class="nav nav-tabs mb-4" id="opsTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-semibold" id="tab-budget" data-bs-toggle="tab"
                    data-bs-target="#pane-budget" type="button" role="tab">
                <i class="bi bi-bar-chart-fill me-1 text-success"></i> Budget
                <span class="badge bg-secondary ms-1"><?= count($budget) ?></span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold" id="tab-planning" data-bs-toggle="tab"
                    data-bs-target="#pane-planning" type="button" role="tab">
                <i class="bi bi-calendar3 me-1 text-primary"></i> Planning
                <span class="badge bg-secondary ms-1"><?= count($planning) ?></span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold" id="tab-materiel" data-bs-toggle="tab"
                    data-bs-target="#pane-materiel" type="button" role="tab">
                <i class="bi bi-box-seam me-1 text-warning"></i> Matériel
                <span class="badge bg-secondary ms-1"><?= count($materiel) ?></span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold" id="tab-facturation" data-bs-toggle="tab"
                    data-bs-target="#pane-facturation" type="button" role="tab">
                <i class="bi bi-receipt me-1 text-info"></i> Facturation
                <span class="badge bg-secondary ms-1"><?= count($facturation) ?></span>
            </button>
        </li>
    </ul>

    <div class="tab-content">

        <!-- ══════════════════════════════════════════════════
             ONGLET BUDGET
        ══════════════════════════════════════════════════ -->
        <div class="tab-pane fade show active" id="pane-budget" role="tabpanel">

            <!-- KPIs -->
            <ul class="row row-cols-2 row-cols-md-4 g-3 list-unstyled mb-4">
                <?php foreach ([
                    ['label'=>'Produits prévisionnels', 'val'=>$budgetTotaux['total_produits_prev']??0, 'color'=>'success', 'icon'=>'bi-arrow-up-circle'],
                    ['label'=>'Charges prévisionnelles','val'=>$budgetTotaux['total_charges_prev'] ??0, 'color'=>'danger',  'icon'=>'bi-arrow-down-circle'],
                    ['label'=>'Résultat prévisionnel',  'val'=>$budgetTotaux['resultat_prev']      ??0, 'color'=>($budgetTotaux['resultat_prev']??0)>=0?'success':'danger', 'icon'=>'bi-calculator'],
                    ['label'=>'Résultat comparatif',    'val'=>$budgetTotaux['resultat_comp']      ??0, 'color'=>($budgetTotaux['resultat_comp']??0)>=0?'success':'danger', 'icon'=>'bi-graph-up'],
                ] as $kpi): ?>
                <li class="col">
                    <article class="card border-0 shadow-sm rounded-3 text-center h-100">
                        <div class="card-body py-3">
                            <i class="bi <?= $kpi['icon'] ?> text-<?= $kpi['color'] ?> fs-4 mb-1 d-block"></i>
                            <p class="text-body-secondary small mb-1"><?= $kpi['label'] ?></p>
                            <p class="fw-bold fs-5 mb-0 text-<?= $kpi['color'] ?>">
                                <?= number_format((float)$kpi['val'], 2, ',', ' ') ?> €
                            </p>
                        </div>
                    </article>
                </li>
                <?php endforeach; ?>
            </ul>

            <div class="d-flex justify-content-end mb-3">
                <button class="btn btn-primary btn-sm fw-semibold shadow-sm"
                        data-bs-toggle="modal" data-bs-target="#modalBudgetCreate">
                    <i class="bi bi-plus-lg me-1"></i> Ajouter une ligne
                </button>
            </div>

            <!-- Produits d'exploitation -->
            <?php if (!empty($budgetProduits)): ?>
            <section class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-success-subtle border-0 fw-bold text-success rounded-top-3">
                    <i class="bi bi-arrow-up-circle me-2"></i>Produits d'exploitation
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th class="ps-3">Catégorie</th><th>Sous-catégorie</th><th>Libellé</th>
                                <th class="text-end">Prévisionnel</th><th class="text-end">Comparatif 2025</th>
                                <th class="text-end">Écart</th><th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($budgetProduits as $bl):
                            $ecart = (float)$bl['previsionnel'] - (float)$bl['comparatif'];
                        ?>
                        <tr>
                            <td class="ps-3 small"><?= htmlspecialchars((string)$bl['categorie'], ENT_QUOTES) ?></td>
                            <td class="small text-body-secondary"><?= htmlspecialchars((string)$bl['sous_categorie'], ENT_QUOTES) ?></td>
                            <td class="fw-medium"><?= htmlspecialchars((string)$bl['libelle'], ENT_QUOTES) ?></td>
                            <td class="text-end text-success fw-bold"><?= number_format((float)$bl['previsionnel'],2,',',' ') ?> €</td>
                            <td class="text-end text-body-secondary"><?= $bl['comparatif'] > 0 ? number_format((float)$bl['comparatif'],2,',',' ').' €' : '—' ?></td>
                            <td class="text-end small <?= $ecart >= 0 ? 'text-success' : 'text-danger' ?>">
                                <?= $bl['comparatif'] > 0 ? ($ecart >= 0 ? '+' : '').number_format($ecart,2,',',' ').' €' : '—' ?>
                            </td>
                            <td class="text-end pe-3">
                                <button class="btn btn-sm btn-outline-secondary py-0 px-2 me-1"
                                        onclick="openBudgetEdit(<?= htmlspecialchars(json_encode($bl), ENT_QUOTES) ?>)">
                                    <i class="bi bi-pencil"></i></button>
                                <form method="POST" class="d-inline"
                                      onsubmit="return confirm('Supprimer cette ligne ?')">
                                    <?= opsForm($eventId, $projetId) ?>
                                    <input type="hidden" name="ops_action" value="budget_delete">
                                    <input type="hidden" name="ligne_id" value="<?= (int)$bl['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger py-0 px-2"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-success fw-bold small">
                            <tr>
                                <td colspan="3" class="ps-3">TOTAL PRODUITS</td>
                                <td class="text-end"><?= number_format((float)($budgetTotaux['total_produits_prev']??0),2,',',' ') ?> €</td>
                                <td class="text-end"><?= number_format((float)($budgetTotaux['total_produits_comp']??0),2,',',' ') ?> €</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </section>
            <?php endif; ?>

            <!-- Charges d'exploitation -->
            <?php if (!empty($budgetCharges)): ?>
            <section class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-danger-subtle border-0 fw-bold text-danger rounded-top-3">
                    <i class="bi bi-arrow-down-circle me-2"></i>Charges d'exploitation
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th class="ps-3">Catégorie</th><th>Sous-catégorie</th><th>Libellé</th>
                                <th class="text-end">Prévisionnel</th><th class="text-end">Comparatif 2025</th>
                                <th class="text-end">Écart</th><th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($budgetCharges as $bl):
                            $ecart = (float)$bl['previsionnel'] - (float)$bl['comparatif'];
                        ?>
                        <tr>
                            <td class="ps-3 small"><?= htmlspecialchars((string)$bl['categorie'], ENT_QUOTES) ?></td>
                            <td class="small text-body-secondary"><?= htmlspecialchars((string)$bl['sous_categorie'], ENT_QUOTES) ?></td>
                            <td class="fw-medium"><?= htmlspecialchars((string)$bl['libelle'], ENT_QUOTES) ?></td>
                            <td class="text-end text-danger fw-bold"><?= number_format((float)$bl['previsionnel'],2,',',' ') ?> €</td>
                            <td class="text-end text-body-secondary"><?= $bl['comparatif'] > 0 ? number_format((float)$bl['comparatif'],2,',',' ').' €' : '—' ?></td>
                            <td class="text-end small <?= $ecart >= 0 ? 'text-success' : 'text-danger' ?>">
                                <?= $bl['comparatif'] > 0 ? ($ecart >= 0 ? '+' : '').number_format($ecart,2,',',' ').' €' : '—' ?>
                            </td>
                            <td class="text-end pe-3">
                                <button class="btn btn-sm btn-outline-secondary py-0 px-2 me-1"
                                        onclick="openBudgetEdit(<?= htmlspecialchars(json_encode($bl), ENT_QUOTES) ?>)">
                                    <i class="bi bi-pencil"></i></button>
                                <form method="POST" class="d-inline"
                                      onsubmit="return confirm('Supprimer cette ligne ?')">
                                    <?= opsForm($eventId, $projetId) ?>
                                    <input type="hidden" name="ops_action" value="budget_delete">
                                    <input type="hidden" name="ligne_id" value="<?= (int)$bl['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger py-0 px-2"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-danger fw-bold small">
                            <tr>
                                <td colspan="3" class="ps-3">TOTAL CHARGES</td>
                                <td class="text-end"><?= number_format((float)($budgetTotaux['total_charges_prev']??0),2,',',' ') ?> €</td>
                                <td class="text-end"><?= number_format((float)($budgetTotaux['total_charges_comp']??0),2,',',' ') ?> €</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </section>
            <?php endif; ?>

            <!-- Résultat -->
            <?php $resPrev = (float)($budgetTotaux['resultat_prev']??0); ?>
            <div class="alert alert-<?= $resPrev >= 0 ? 'success' : 'danger' ?> d-flex justify-content-between align-items-center fw-bold shadow-sm">
                <span><i class="bi bi-calculator me-2"></i>RÉSULTAT D'EXPLOITATION (prévisionnel)</span>
                <span class="fs-5"><?= number_format($resPrev,2,',',' ') ?> €</span>
            </div>

            <?php if (empty($budget)): ?>
            <p class="text-body-secondary text-center py-4">
                <i class="bi bi-bar-chart fs-2 d-block mb-2 opacity-50"></i>
                Aucune ligne de budget. Ajoute ta première ligne !
            </p>
            <?php endif; ?>
        </div>

        <!-- ══════════════════════════════════════════════════
             ONGLET PLANNING
        ══════════════════════════════════════════════════ -->
        <div class="tab-pane fade" id="pane-planning" role="tabpanel">

            <div class="d-flex justify-content-end mb-3">
                <button class="btn btn-primary btn-sm fw-semibold shadow-sm"
                        data-bs-toggle="modal" data-bs-target="#modalPlanningCreate">
                    <i class="bi bi-plus-lg me-1"></i> Ajouter une tâche
                </button>
            </div>

            <?php if (empty($planning)): ?>
            <p class="text-body-secondary text-center py-5">
                <i class="bi bi-calendar-x fs-2 d-block mb-2 opacity-50"></i>Aucune tâche de planning.
            </p>
            <?php else: ?>
            <div class="card border-0 shadow-sm rounded-3">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark small">
                            <tr>
                                <th class="ps-3">#</th>
                                <th>Tâche</th>
                                <th>Statut</th>
                                <th>Début</th>
                                <th>Fin</th>
                                <th>Note</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($planning as $pl):
                            $pSt = $planningStatuts[$pl['statut']] ?? ['label'=>$pl['statut'],'color'=>'secondary'];
                        ?>
                        <tr>
                            <td class="ps-3 text-body-secondary small"><?= (int)$pl['ordre'] ?: '—' ?></td>
                            <td class="fw-medium"><?= htmlspecialchars((string)$pl['tache'], ENT_QUOTES) ?></td>
                            <td>
                                <span class="badge rounded-pill bg-<?= $pSt['color'] ?>">
                                    <?= htmlspecialchars($pSt['label'], ENT_QUOTES) ?>
                                </span>
                            </td>
                            <td class="small"><?= !empty($pl['date_debut']) ? date('d/m/Y', strtotime($pl['date_debut'])) : '—' ?></td>
                            <td class="small"><?= !empty($pl['date_fin'])   ? date('d/m/Y', strtotime($pl['date_fin']))   : '—' ?></td>
                            <td class="small text-body-secondary"><?= htmlspecialchars((string)($pl['note']??''), ENT_QUOTES) ?></td>
                            <td class="text-end pe-3">
                                <button class="btn btn-sm btn-outline-secondary py-0 px-2 me-1"
                                        onclick="openPlanningEdit(<?= htmlspecialchars(json_encode($pl), ENT_QUOTES) ?>)">
                                    <i class="bi bi-pencil"></i></button>
                                <form method="POST" class="d-inline"
                                      onsubmit="return confirm('Supprimer cette tâche ?')">
                                    <?= opsForm($eventId, $projetId) ?>
                                    <input type="hidden" name="ops_action" value="planning_delete">
                                    <input type="hidden" name="ligne_id" value="<?= (int)$pl['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger py-0 px-2"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- ══════════════════════════════════════════════════
             ONGLET MATÉRIEL
        ══════════════════════════════════════════════════ -->
        <div class="tab-pane fade" id="pane-materiel" role="tabpanel">

            <div class="d-flex justify-content-end mb-3">
                <button class="btn btn-warning btn-sm fw-semibold shadow-sm text-dark"
                        data-bs-toggle="modal" data-bs-target="#modalMaterielCreate">
                    <i class="bi bi-plus-lg me-1"></i> Ajouter un matériel
                </button>
            </div>

            <?php if (empty($materiel)): ?>
            <p class="text-body-secondary text-center py-5">
                <i class="bi bi-box-seam fs-2 d-block mb-2 opacity-50"></i>Aucun matériel enregistré.
            </p>
            <?php else: ?>
            <div class="card border-0 shadow-sm rounded-3">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark small">
                            <tr>
                                <th class="ps-3">Nom</th><th>Qté</th><th>Fournisseur</th>
                                <th>Date In</th><th>Date Out</th><th>Commentaire</th><th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($materiel as $mat): ?>
                        <tr>
                            <td class="ps-3 fw-medium"><?= htmlspecialchars((string)$mat['nom'], ENT_QUOTES) ?></td>
                            <td><?= htmlspecialchars((string)$mat['quantite'], ENT_QUOTES) ?></td>
                            <td class="text-body-secondary small"><?= htmlspecialchars((string)($mat['fournisseur']??'—'), ENT_QUOTES) ?></td>
                            <td class="small"><?= !empty($mat['date_in'])  ? date('d/m/Y', strtotime($mat['date_in']))  : '—' ?></td>
                            <td class="small"><?= !empty($mat['date_out']) ? date('d/m/Y', strtotime($mat['date_out'])) : '—' ?></td>
                            <td class="small text-body-secondary"><?= htmlspecialchars((string)($mat['commentaire']??''), ENT_QUOTES) ?></td>
                            <td class="text-end pe-3">
                                <button class="btn btn-sm btn-outline-secondary py-0 px-2 me-1"
                                        onclick="openMaterielEdit(<?= htmlspecialchars(json_encode($mat), ENT_QUOTES) ?>)">
                                    <i class="bi bi-pencil"></i></button>
                                <form method="POST" class="d-inline"
                                      onsubmit="return confirm('Supprimer ce matériel ?')">
                                    <?= opsForm($eventId, $projetId) ?>
                                    <input type="hidden" name="ops_action" value="materiel_delete">
                                    <input type="hidden" name="ligne_id" value="<?= (int)$mat['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger py-0 px-2"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- ══════════════════════════════════════════════════
             ONGLET FACTURATION
        ══════════════════════════════════════════════════ -->
        <div class="tab-pane fade" id="pane-facturation" role="tabpanel">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="fw-bold text-body-secondary">
                    Total général :
                    <span class="text-primary fs-5 fw-bold ms-1">
                        <?= number_format($totalFacturation, 2, ',', ' ') ?> €
                    </span>
                </div>
                <button class="btn btn-info btn-sm fw-semibold shadow-sm text-white"
                        data-bs-toggle="modal" data-bs-target="#modalFacturationCreate">
                    <i class="bi bi-plus-lg me-1"></i> Ajouter une ligne
                </button>
            </div>

            <?php if (empty($facturation)): ?>
            <p class="text-body-secondary text-center py-5">
                <i class="bi bi-receipt fs-2 d-block mb-2 opacity-50"></i>Aucune ligne de facturation.
            </p>
            <?php else: ?>
            <div class="card border-0 shadow-sm rounded-3">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark small">
                            <tr>
                                <th class="ps-3">Catégorie</th><th>Poste</th><th>Prestataire</th>
                                <th>Contact</th><th>Tél</th><th>Mail</th>
                                <th class="text-end">P.U</th><th class="text-end">Qté</th>
                                <th class="text-end">Total</th>
                                <th class="text-center">Devis</th><th class="text-center">Facture</th>
                                <th class="text-center">Virement</th><th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($facturation as $fac):
                            $tot = (float)$fac['prix_unitaire'] * (float)$fac['quantite'];
                        ?>
                        <tr>
                            <td class="ps-3 small"><?= htmlspecialchars((string)($fac['categorie']??''), ENT_QUOTES) ?></td>
                            <td class="fw-medium small"><?= htmlspecialchars((string)($fac['poste']??''), ENT_QUOTES) ?></td>
                            <td class="small"><?= htmlspecialchars((string)($fac['prestataire']??''), ENT_QUOTES) ?></td>
                            <td class="small"><?= htmlspecialchars((string)($fac['contact']??''), ENT_QUOTES) ?></td>
                            <td class="small"><?= htmlspecialchars((string)($fac['telephone']??''), ENT_QUOTES) ?></td>
                            <td class="small">
                                <?php if (!empty($fac['mail'])): ?>
                                <a href="mailto:<?= htmlspecialchars($fac['mail'], ENT_QUOTES) ?>" class="text-decoration-none text-primary">
                                    <?= htmlspecialchars($fac['mail'], ENT_QUOTES) ?></a>
                                <?php else: echo '—'; endif; ?>
                            </td>
                            <td class="text-end small"><?= number_format((float)$fac['prix_unitaire'],2,',',' ') ?> €</td>
                            <td class="text-end small"><?= htmlspecialchars((string)$fac['quantite'], ENT_QUOTES) ?></td>
                            <td class="text-end fw-bold"><?= number_format($tot,2,',',' ') ?> €</td>
                            <td class="text-center">
                                <?= $fac['statut_devis']    ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-body-secondary"></i>' ?>
                            </td>
                            <td class="text-center">
                                <?= $fac['statut_facture']  ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-body-secondary"></i>' ?>
                            </td>
                            <td class="text-center">
                                <?= $fac['statut_virement'] ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-body-secondary"></i>' ?>
                            </td>
                            <td class="text-end pe-3">
                                <button class="btn btn-sm btn-outline-secondary py-0 px-2 me-1"
                                        onclick="openFacturationEdit(<?= htmlspecialchars(json_encode($fac), ENT_QUOTES) ?>)">
                                    <i class="bi bi-pencil"></i></button>
                                <form method="POST" class="d-inline"
                                      onsubmit="return confirm('Supprimer cette ligne ?')">
                                    <?= opsForm($eventId, $projetId) ?>
                                    <input type="hidden" name="ops_action" value="facturation_delete">
                                    <input type="hidden" name="ligne_id" value="<?= (int)$fac['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger py-0 px-2"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-primary fw-bold small">
                            <tr>
                                <td colspan="8" class="ps-3 text-end">TOTAL GÉNÉRAL</td>
                                <td class="text-end"><?= number_format($totalFacturation,2,',',' ') ?> €</td>
                                <td colspan="4"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>

    </div><!-- /.tab-content -->
    <?php endif; // contexte sélectionné ?>

</div><!-- /.container-fluid -->


<!-- ══════════════════════════════════════════════════════
     MODALS
════════════════════════════════════════════════════════ -->
<?php
$statuts_planning_opts = ['wip','en_cours','valide','maj','devis','visuels','bat','prod','annule'];
$statuts_labels        = ['wip'=>'WIP','en_cours'=>'En cours','valide'=>'Validé','maj'=>'Maj',
                          'devis'=>'Devis','visuels'=>'Visuels','bat'=>'BAT','prod'=>'Prod','annule'=>'Annulé'];
?>

<!-- Modal Budget Create -->
<div class="modal fade" id="modalBudgetCreate" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header border-0"><h5 class="modal-title fw-bold"><i class="bi bi-bar-chart-fill me-2 text-success"></i>Nouvelle ligne de budget</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <form method="POST">
          <?= opsForm($eventId, $projetId) ?>
          <input type="hidden" name="ops_action" value="budget_create">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
              <select name="type" class="form-select rounded-3">
                <option value="produit">Produit d'exploitation</option>
                <option value="charge" selected>Charge d'exploitation</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Catégorie</label>
              <input type="text" name="categorie" class="form-control rounded-3" placeholder="ex: Hébergement">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Sous-catégorie</label>
              <input type="text" name="sous_categorie" class="form-control rounded-3" placeholder="ex: Logement">
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Libellé <span class="text-danger">*</span></label>
              <input type="text" name="libelle" class="form-control rounded-3" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Prévisionnel (€)</label>
              <input type="number" step="0.01" name="previsionnel" class="form-control rounded-3" value="0">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Comparatif 2025 (€)</label>
              <input type="number" step="0.01" name="comparatif" class="form-control rounded-3" value="0">
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Note</label>
              <input type="text" name="note" class="form-control rounded-3">
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Annuler</button>
            <button type="submit" class="btn btn-success rounded-3 fw-semibold"><i class="bi bi-plus-lg me-1"></i>Ajouter</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal Budget Edit -->
<div class="modal fade" id="modalBudgetEdit" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header border-0"><h5 class="modal-title fw-bold"><i class="bi bi-pencil me-2 text-primary"></i>Modifier la ligne</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <form method="POST">
          <?= opsForm($eventId, $projetId) ?>
          <input type="hidden" name="ops_action" value="budget_update">
          <input type="hidden" name="ligne_id" id="be-id">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label fw-semibold">Type</label>
              <select name="type" id="be-type" class="form-select rounded-3">
                <option value="produit">Produit d'exploitation</option>
                <option value="charge">Charge d'exploitation</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Catégorie</label>
              <input type="text" name="categorie" id="be-cat" class="form-control rounded-3">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Sous-catégorie</label>
              <input type="text" name="sous_categorie" id="be-scat" class="form-control rounded-3">
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Libellé</label>
              <input type="text" name="libelle" id="be-lib" class="form-control rounded-3" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Prévisionnel (€)</label>
              <input type="number" step="0.01" name="previsionnel" id="be-prev" class="form-control rounded-3">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Comparatif 2025 (€)</label>
              <input type="number" step="0.01" name="comparatif" id="be-comp" class="form-control rounded-3">
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Note</label>
              <input type="text" name="note" id="be-note" class="form-control rounded-3">
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

<!-- Modal Planning Create -->
<div class="modal fade" id="modalPlanningCreate" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header border-0"><h5 class="modal-title fw-bold"><i class="bi bi-calendar-plus me-2 text-primary"></i>Nouvelle tâche planning</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <form method="POST">
          <?= opsForm($eventId, $projetId) ?>
          <input type="hidden" name="ops_action" value="planning_create">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label fw-semibold">Tâche <span class="text-danger">*</span></label>
              <input type="text" name="tache" class="form-control rounded-3" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Statut</label>
              <select name="statut" class="form-select rounded-3">
                <?php foreach ($statuts_planning_opts as $s): ?>
                <option value="<?= $s ?>"><?= $statuts_labels[$s] ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Ordre</label>
              <input type="number" name="ordre" class="form-control rounded-3" value="0">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Date début</label>
              <input type="date" name="date_debut" class="form-control rounded-3">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Date fin</label>
              <input type="date" name="date_fin" class="form-control rounded-3">
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Note</label>
              <textarea name="note" class="form-control rounded-3" rows="2"></textarea>
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Annuler</button>
            <button type="submit" class="btn btn-primary rounded-3 fw-semibold"><i class="bi bi-plus-lg me-1"></i>Ajouter</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal Planning Edit -->
<div class="modal fade" id="modalPlanningEdit" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header border-0"><h5 class="modal-title fw-bold"><i class="bi bi-pencil me-2 text-primary"></i>Modifier la tâche</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <form method="POST">
          <?= opsForm($eventId, $projetId) ?>
          <input type="hidden" name="ops_action" value="planning_update">
          <input type="hidden" name="ligne_id" id="pe-id">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label fw-semibold">Tâche</label>
              <input type="text" name="tache" id="pe-tache" class="form-control rounded-3" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Statut</label>
              <select name="statut" id="pe-statut" class="form-select rounded-3">
                <?php foreach ($statuts_planning_opts as $s): ?>
                <option value="<?= $s ?>"><?= $statuts_labels[$s] ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Ordre</label>
              <input type="number" name="ordre" id="pe-ordre" class="form-control rounded-3">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Date début</label>
              <input type="date" name="date_debut" id="pe-debut" class="form-control rounded-3">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Date fin</label>
              <input type="date" name="date_fin" id="pe-fin" class="form-control rounded-3">
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Note</label>
              <textarea name="note" id="pe-note" class="form-control rounded-3" rows="2"></textarea>
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

<!-- Modal Matériel Create -->
<div class="modal fade" id="modalMaterielCreate" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header border-0"><h5 class="modal-title fw-bold"><i class="bi bi-box-seam me-2 text-warning"></i>Nouveau matériel</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <form method="POST">
          <?= opsForm($eventId, $projetId) ?>
          <input type="hidden" name="ops_action" value="materiel_create">
          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label fw-semibold">Nom <span class="text-danger">*</span></label>
              <input type="text" name="nom" class="form-control rounded-3" required>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Quantité</label>
              <input type="number" step="0.5" name="quantite" class="form-control rounded-3" value="1">
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Fournisseur</label>
              <input type="text" name="fournisseur" class="form-control rounded-3">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Date In</label>
              <input type="date" name="date_in" class="form-control rounded-3">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Date Out</label>
              <input type="date" name="date_out" class="form-control rounded-3">
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Commentaire</label>
              <textarea name="commentaire" class="form-control rounded-3" rows="2"></textarea>
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Annuler</button>
            <button type="submit" class="btn btn-warning rounded-3 fw-semibold text-dark"><i class="bi bi-plus-lg me-1"></i>Ajouter</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal Matériel Edit -->
<div class="modal fade" id="modalMaterielEdit" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header border-0"><h5 class="modal-title fw-bold"><i class="bi bi-pencil me-2 text-primary"></i>Modifier le matériel</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <form method="POST">
          <?= opsForm($eventId, $projetId) ?>
          <input type="hidden" name="ops_action" value="materiel_update">
          <input type="hidden" name="ligne_id" id="me-id">
          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label fw-semibold">Nom</label>
              <input type="text" name="nom" id="me-nom" class="form-control rounded-3" required>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Quantité</label>
              <input type="number" step="0.5" name="quantite" id="me-qte" class="form-control rounded-3">
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Fournisseur</label>
              <input type="text" name="fournisseur" id="me-four" class="form-control rounded-3">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Date In</label>
              <input type="date" name="date_in" id="me-din" class="form-control rounded-3">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Date Out</label>
              <input type="date" name="date_out" id="me-dout" class="form-control rounded-3">
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Commentaire</label>
              <textarea name="commentaire" id="me-comm" class="form-control rounded-3" rows="2"></textarea>
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

<!-- Modal Facturation Create -->
<div class="modal fade" id="modalFacturationCreate" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header border-0"><h5 class="modal-title fw-bold"><i class="bi bi-receipt me-2 text-info"></i>Nouvelle ligne de facturation</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <form method="POST">
          <?= opsForm($eventId, $projetId) ?>
          <input type="hidden" name="ops_action" value="facturation_create">
          <div class="row g-3">
            <div class="col-md-4"><label class="form-label fw-semibold">Catégorie</label>
              <input type="text" name="categorie" class="form-control rounded-3" placeholder="ex: Hébergement"></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Poste</label>
              <input type="text" name="poste" class="form-control rounded-3" placeholder="ex: Logement"></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Prestataire</label>
              <input type="text" name="prestataire" class="form-control rounded-3"></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Contact</label>
              <input type="text" name="contact" class="form-control rounded-3"></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Téléphone</label>
              <input type="text" name="telephone" class="form-control rounded-3"></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Mail</label>
              <input type="email" name="mail" class="form-control rounded-3"></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Prix unitaire (€)</label>
              <input type="number" step="0.01" name="prix_unitaire" class="form-control rounded-3" value="0" id="fc-pu" oninput="updateFcTotal()"></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Quantité</label>
              <input type="number" step="0.01" name="quantite" class="form-control rounded-3" value="1" id="fc-qte" oninput="updateFcTotal()"></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Total (calculé)</label>
              <input type="text" id="fc-total" class="form-control rounded-3 bg-body-secondary" readonly value="0,00 €"></div>
            <div class="col-md-3 d-flex align-items-end gap-3 pb-1">
              <div class="form-check"><input class="form-check-input" type="checkbox" name="statut_devis"    id="fc-devis">
                <label class="form-check-label" for="fc-devis">Devis</label></div>
              <div class="form-check"><input class="form-check-input" type="checkbox" name="statut_facture"  id="fc-facture">
                <label class="form-check-label" for="fc-facture">Facture</label></div>
              <div class="form-check"><input class="form-check-input" type="checkbox" name="statut_virement" id="fc-virement">
                <label class="form-check-label" for="fc-virement">Virement</label></div>
            </div>
            <div class="col-12"><label class="form-label fw-semibold">Note</label>
              <input type="text" name="note" class="form-control rounded-3"></div>
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

<!-- Modal Facturation Edit -->
<div class="modal fade" id="modalFacturationEdit" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header border-0"><h5 class="modal-title fw-bold"><i class="bi bi-pencil me-2 text-primary"></i>Modifier la facturation</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <form method="POST">
          <?= opsForm($eventId, $projetId) ?>
          <input type="hidden" name="ops_action" value="facturation_update">
          <input type="hidden" name="ligne_id" id="fe-id">
          <div class="row g-3">
            <div class="col-md-4"><label class="form-label fw-semibold">Catégorie</label><input type="text" name="categorie"   id="fe-cat"  class="form-control rounded-3"></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Poste</label><input type="text" name="poste"           id="fe-poste" class="form-control rounded-3"></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Prestataire</label><input type="text" name="prestataire" id="fe-prest" class="form-control rounded-3"></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Contact</label><input type="text" name="contact"       id="fe-cont" class="form-control rounded-3"></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Téléphone</label><input type="text" name="telephone"   id="fe-tel"  class="form-control rounded-3"></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Mail</label><input type="email" name="mail"            id="fe-mail" class="form-control rounded-3"></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Prix unitaire (€)</label>
              <input type="number" step="0.01" name="prix_unitaire" id="fe-pu" class="form-control rounded-3" oninput="updateFeTotal()"></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Quantité</label>
              <input type="number" step="0.01" name="quantite" id="fe-qte" class="form-control rounded-3" oninput="updateFeTotal()"></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Total</label>
              <input type="text" id="fe-total" class="form-control rounded-3 bg-body-secondary" readonly></div>
            <div class="col-md-3 d-flex align-items-end gap-3 pb-1">
              <div class="form-check"><input class="form-check-input" type="checkbox" name="statut_devis"    id="fe-devis">
                <label class="form-check-label" for="fe-devis">Devis</label></div>
              <div class="form-check"><input class="form-check-input" type="checkbox" name="statut_facture"  id="fe-facture">
                <label class="form-check-label" for="fe-facture">Facture</label></div>
              <div class="form-check"><input class="form-check-input" type="checkbox" name="statut_virement" id="fe-virement">
                <label class="form-check-label" for="fe-virement">Virement</label></div>
            </div>
            <div class="col-12"><label class="form-label fw-semibold">Note</label><input type="text" name="note" id="fe-note" class="form-control rounded-3"></div>
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