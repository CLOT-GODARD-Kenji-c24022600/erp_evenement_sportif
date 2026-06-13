<?php

/**
 * YES – Your Event Solution
 * Vue : Opérationnel — Budget / Planning / Matériel / Facturation / Pré-production
 *
 * @file operationnel.php
 * @version 2.0  –  2026
 */

declare(strict_types=1);

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

// Calcul totaux matériel loué / acheté
$totalLoue   = $materielTotaux['total_loue']   ?? 0;
$totalAchete = $materielTotaux['total_achete'] ?? 0;
$totalMat    = $materielTotaux['total_global'] ?? 0;

// Planning Gantt : tâches avec dates
$planningAvecDates = array_filter($planning, fn($p) => !empty($p['date_debut']));
?>

<div class="container-fluid py-4"
     id="ops-container"
     data-restore-tab="<?= htmlspecialchars((string)($restoreTab ?? ''), ENT_QUOTES) ?>">

    <header class="d-flex flex-wrap align-items-center gap-3 mb-4">
        <hgroup class="flex-grow-1">
            <h1 class="fw-bold fs-4 mb-0">
                <i class="bi bi-clipboard2-data-fill me-2 text-primary"></i>
                Opérationnel
                <?php if ($contextLabel): ?>
                    <span class="text-primary">— <?= htmlspecialchars($contextLabel, ENT_QUOTES) ?></span>
                <?php endif; ?>
            </h1>
            <p class="text-body-secondary small mb-0">Budget · Planning · Matériel · Facturation · Pré-production</p>
        </hgroup>

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

    <?php
function exportUrl(string $type, int $eventId, int $projetId, string $format): string {
    $base = "/export?type={$type}&format={$format}";
    if ($eventId  > 0) $base .= "&event_id={$eventId}";
    if ($projetId > 0) $base .= "&projet_id={$projetId}";
    return $base;
}
?>
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

    <?php if ($projetId > 0 && !empty($projetFinance)): ?>
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-3 px-4">
            <h2 class="fw-bold fs-6 mb-0">
                <i class="bi bi-cash-coin me-2 text-success"></i>Finance du projet
            </h2>
            <a href="/projet_detail?id=<?= $projetId ?>" class="btn btn-sm btn-outline-secondary rounded-3">
                <i class="bi bi-arrow-right me-1"></i>Voir le projet
            </a>
        </div>
        <div class="card-body pt-2 pb-3">
            <ul class="row row-cols-2 row-cols-md-4 g-3 list-unstyled mb-0">
                <?php
                $pfItems = [
                    ['label'=>'Budget projet', 'val'=>(float)($projetFinance['budget']??0),   'color'=>'primary', 'icon'=>'bi-wallet2',          'show'=>($projetFinance['budget']??0)>0],
                    ['label'=>'Recettes',       'val'=>(float)($projetFinance['recettes']??0),'color'=>'success', 'icon'=>'bi-arrow-up-circle',   'show'=>true],
                    ['label'=>'Dépenses',       'val'=>(float)($projetFinance['depenses']??0),'color'=>'danger',  'icon'=>'bi-arrow-down-circle',  'show'=>true],
                    ['label'=>'Solde',          'val'=>(float)($projetFinance['solde']??0),   'color'=>(($projetFinance['solde']??0)>=0?'success':'danger'), 'icon'=>'bi-calculator','show'=>true],
                ];
                foreach ($pfItems as $kpi): if (!$kpi['show']) continue;
                ?>
                <li class="col">
                    <div class="text-center py-2">
                        <i class="bi <?= $kpi['icon'] ?> text-<?= $kpi['color'] ?> fs-4 d-block mb-1"></i>
                        <p class="text-body-secondary small mb-1"><?= $kpi['label'] ?></p>
                        <p class="fw-bold fs-6 mb-0 text-<?= $kpi['color'] ?>">
                            <?= ($kpi['label']==='Solde'&&$kpi['val']>=0?'+':'').number_format($kpi['val'],2,',',' ') ?> €
                        </p>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($eventId > 0 && $eventData): ?>
    <?php $hasLinks = !empty($eventData['drive_url']) || !empty($eventData['drive_doc_url']) || !empty($eventData['maps_url']); ?>
    <div class="d-flex flex-wrap gap-2 mb-3 align-items-center">
        <?php if (!empty($eventData['drive_url'])): ?>
        <a href="<?= htmlspecialchars($eventData['drive_url'], ENT_QUOTES) ?>" target="_blank"
           class="btn btn-sm btn-outline-primary rounded-3">
            <i class="bi bi-cloud me-1"></i>Google Drive
        </a>
        <?php endif; ?>
        <?php if (!empty($eventData['drive_doc_url'])): ?>
        <a href="<?= htmlspecialchars($eventData['drive_doc_url'], ENT_QUOTES) ?>" target="_blank"
           class="btn btn-sm btn-outline-success rounded-3">
            <i class="bi bi-file-earmark-text me-1"></i>Google Doc
        </a>
        <?php endif; ?>
        <?php if (!empty($eventData['maps_url'])): ?>
        <a href="<?= htmlspecialchars($eventData['maps_url'], ENT_QUOTES) ?>" target="_blank"
           class="btn btn-sm btn-outline-danger rounded-3">
            <i class="bi bi-geo-alt me-1"></i>Google Maps
        </a>
        <?php endif; ?>
        <button class="btn btn-sm btn-outline-secondary rounded-3"
                data-bs-toggle="modal" data-bs-target="#modalDriveLinks">
            <i class="bi bi-pencil me-1"></i><?= $hasLinks ? 'Modifier les liens' : 'Lier Google Drive / Maps' ?>
        </button>
    </div>
    <?php endif; ?>

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
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold" id="tab-preprod" data-bs-toggle="tab"
                    data-bs-target="#pane-preprod" type="button" role="tab">
                <i class="bi bi-hammer me-1 text-orange" style="color:#fd7e14"></i> Pré-production
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold" id="tab-contacts" data-bs-toggle="tab"
                    data-bs-target="#pane-contacts-ops" type="button" role="tab">
                <i class="bi bi-person-lines-fill me-1 text-secondary"></i> Contacts
                <span class="badge bg-secondary ms-1"><?= count($contactsLies) ?></span>
            </button>
        </li>
    </ul>

    <div class="tab-content">

        <div class="tab-pane fade show active" id="pane-budget" role="tabpanel">

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

            <div class="alert alert-info border-0 shadow-sm mb-4 d-flex justify-content-between align-items-center">
                <span><i class="bi bi-receipt me-2"></i><strong>Total facturation réelle :</strong></span>
                <span class="fw-bold fs-5"><?= number_format($totalFacturation, 2, ',', ' ') ?> €</span>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex gap-2">
                    <a href="<?= exportUrl('budget', $eventId, $projetId, 'csv') ?>"
                       class="btn btn-sm btn-outline-success rounded-3" title="Exporter en Excel/CSV">
                        <i class="bi bi-file-earmark-excel me-1"></i>Excel
                    </a>
                    <a href="<?= exportUrl('budget', $eventId, $projetId, 'pdf') ?>"
                       class="btn btn-sm btn-outline-danger rounded-3" title="Exporter en PDF"
                       target="_blank">
                        <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                    </a>
                </div>
                <button class="btn btn-primary btn-sm fw-semibold shadow-sm"
                        data-bs-toggle="modal" data-bs-target="#modalBudgetCreate">
                    <i class="bi bi-plus-lg me-1"></i> Ajouter une ligne
                </button>
            </div>

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
                                <th>Fournisseur</th><th>Sponsor</th>
                                <th class="text-end">Prévisionnel</th><th class="text-end">Comparatif</th>
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
                            <td class="small text-body-secondary"><?= htmlspecialchars((string)($bl['fournisseur']??'—'), ENT_QUOTES) ?></td>
                            <td class="small text-body-secondary"><?= htmlspecialchars((string)($bl['sponsor']??'—'), ENT_QUOTES) ?></td>
                            <td class="text-end text-success fw-bold"><?= number_format((float)$bl['previsionnel'],2,',',' ') ?> €</td>
                            <td class="text-end text-body-secondary"><?= $bl['comparatif'] > 0 ? number_format((float)$bl['comparatif'],2,',',' ').' €' : '—' ?></td>
                            <td class="text-end small <?= $ecart >= 0 ? 'text-success' : 'text-danger' ?>">
                                <?= $bl['comparatif'] > 0 ? ($ecart >= 0 ? '+' : '').number_format($ecart,2,',',' ').' €' : '—' ?>
                            </td>
                            <td class="text-end pe-3">
                                <button class="btn btn-sm btn-outline-secondary py-0 px-2 me-1"
                                        onclick="openBudgetEdit(<?= htmlspecialchars(json_encode($bl), ENT_QUOTES) ?>)">
                                    <i class="bi bi-pencil"></i></button>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Supprimer ?')">
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
                                <td colspan="5" class="ps-3">TOTAL PRODUITS</td>
                                <td class="text-end"><?= number_format((float)($budgetTotaux['total_produits_prev']??0),2,',',' ') ?> €</td>
                                <td class="text-end"><?= number_format((float)($budgetTotaux['total_produits_comp']??0),2,',',' ') ?> €</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </section>
            <?php endif; ?>

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
                                <th>Fournisseur</th><th>Sponsor</th>
                                <th class="text-end">Prévisionnel</th><th class="text-end">Comparatif</th>
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
                            <td class="small text-body-secondary"><?= htmlspecialchars((string)($bl['fournisseur']??'—'), ENT_QUOTES) ?></td>
                            <td class="small text-body-secondary"><?= htmlspecialchars((string)($bl['sponsor']??'—'), ENT_QUOTES) ?></td>
                            <td class="text-end text-danger fw-bold"><?= number_format((float)$bl['previsionnel'],2,',',' ') ?> €</td>
                            <td class="text-end text-body-secondary"><?= $bl['comparatif'] > 0 ? number_format((float)$bl['comparatif'],2,',',' ').' €' : '—' ?></td>
                            <td class="text-end small <?= $ecart >= 0 ? 'text-success' : 'text-danger' ?>">
                                <?= $bl['comparatif'] > 0 ? ($ecart >= 0 ? '+' : '').number_format($ecart,2,',',' ').' €' : '—' ?>
                            </td>
                            <td class="text-end pe-3">
                                <button class="btn btn-sm btn-outline-secondary py-0 px-2 me-1"
                                        onclick="openBudgetEdit(<?= htmlspecialchars(json_encode($bl), ENT_QUOTES) ?>)">
                                    <i class="bi bi-pencil"></i></button>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Supprimer ?')">
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
                                <td colspan="5" class="ps-3">TOTAL CHARGES</td>
                                <td class="text-end"><?= number_format((float)($budgetTotaux['total_charges_prev']??0),2,',',' ') ?> €</td>
                                <td class="text-end"><?= number_format((float)($budgetTotaux['total_charges_comp']??0),2,',',' ') ?> €</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </section>
            <?php endif; ?>

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

            <?php if ($totalMat > 0): ?>
            <hr class="my-4 opacity-25">
            <h5 class="fw-bold mb-3"><i class="bi bi-box-seam me-2 text-warning"></i>Budget matériel</h5>
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-3 text-center py-3">
                        <i class="bi bi-arrow-repeat text-warning fs-4 d-block mb-1"></i>
                        <p class="small text-body-secondary mb-1">Matériel loué</p>
                        <p class="fw-bold fs-5 mb-0"><?= number_format((float)$totalLoue,2,',',' ') ?> €</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-3 text-center py-3">
                        <i class="bi bi-bag-check text-primary fs-4 d-block mb-1"></i>
                        <p class="small text-body-secondary mb-1">Matériel acheté</p>
                        <p class="fw-bold fs-5 mb-0"><?= number_format((float)$totalAchete,2,',',' ') ?> €</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-3 text-center py-3">
                        <i class="bi bi-cash-stack text-dark fs-4 d-block mb-1"></i>
                        <p class="small text-body-secondary mb-1">Total matériel</p>
                        <p class="fw-bold fs-5 mb-0"><?= number_format((float)$totalMat,2,',',' ') ?> €</p>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="#pane-materiel" class="btn btn-sm btn-outline-warning rounded-3"
                   onclick="bootstrap.Tab.getOrCreateInstance(document.getElementById('tab-materiel')).show(); return false;">
                    <i class="bi bi-box-seam me-1"></i>Voir le détail matériel
                </a>
                <form method="POST" class="d-inline">
                    <?= opsForm($eventId, $projetId) ?>
                    <input type="hidden" name="ops_action" value="budget_sync_mat">
                    <button type="submit" class="btn btn-sm btn-outline-success rounded-3 fw-semibold">
                        <i class="bi bi-arrow-repeat me-1"></i>Synchroniser vers les charges
                    </button>
                </form>
            </div>
            <?php endif; ?>

            <?php if ($totalFacturation > 0): ?>
            <hr class="my-4 opacity-25">
            <h5 class="fw-bold mb-3"><i class="bi bi-receipt me-2 text-info"></i>Facturation réelle</h5>
            <div class="d-flex align-items-center justify-content-between card border-0 shadow-sm rounded-3 p-3 mb-3">
                <div>
                    <p class="mb-1 fw-semibold">Total facturé</p>
                    <p class="small text-body-secondary mb-0"><?= count($facturation) ?> ligne<?= count($facturation)>1?'s':'' ?></p>
                </div>
                <p class="fw-bold fs-4 mb-0 text-info"><?= number_format($totalFacturation,2,',',' ') ?> €</p>
            </div>
            <?php if (!empty($budgetTotaux['total_charges_prev']) && $budgetTotaux['total_charges_prev'] > 0):
                $ecartBudget = (float)$budgetTotaux['total_charges_prev'] - $totalFacturation;
            ?>
            <div class="alert alert-<?= $ecartBudget >= 0 ? 'success' : 'danger' ?> border-0 shadow-sm d-flex justify-content-between">
                <span><i class="bi bi-calculator me-2"></i>Écart budget charges / facturé</span>
                <strong><?= ($ecartBudget >= 0 ? '+' : '') . number_format($ecartBudget,2,',',' ') ?> €</strong>
            </div>
            <?php endif; ?>
            <div class="d-flex gap-2 flex-wrap">
                <a href="#pane-facturation" class="btn btn-sm btn-outline-info rounded-3"
                   onclick="bootstrap.Tab.getOrCreateInstance(document.getElementById('tab-facturation')).show(); return false;">
                    <i class="bi bi-receipt me-1"></i>Voir le détail facturation
                </a>
                <form method="POST" class="d-inline">
                    <?= opsForm($eventId, $projetId) ?>
                    <input type="hidden" name="ops_action" value="budget_sync_fact">
                    <button type="submit" class="btn btn-sm btn-outline-success rounded-3 fw-semibold">
                        <i class="bi bi-arrow-repeat me-1"></i>Synchroniser vers les charges
                    </button>
                </form>
            </div>
            <?php endif; ?>

        </div>

        <div class="tab-pane fade" id="pane-planning" role="tabpanel">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-secondary active" id="btn-view-list"
                            onclick="switchPlanningView('list')">
                        <i class="bi bi-list-ul me-1"></i>Liste
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" id="btn-view-calendar"
                            onclick="switchPlanningView('calendar')">
                        <i class="bi bi-calendar3 me-1"></i>Calendrier
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" id="btn-view-gantt"
                            onclick="switchPlanningView('gantt')">
                        <i class="bi bi-bar-chart-steps me-1"></i>Gantt
                    </button>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?= exportUrl('planning', $eventId, $projetId, 'csv') ?>"
                       class="btn btn-sm btn-outline-success rounded-3" title="Exporter en Excel/CSV">
                        <i class="bi bi-file-earmark-excel me-1"></i>Excel
                    </a>
                    <a href="<?= exportUrl('planning', $eventId, $projetId, 'pdf') ?>"
                       class="btn btn-sm btn-outline-danger rounded-3" title="Exporter en PDF"
                       target="_blank">
                        <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                    </a>
                    <button class="btn btn-primary btn-sm fw-semibold shadow-sm"
                        data-bs-toggle="modal" data-bs-target="#modalPlanningCreate">
                        <i class="bi bi-plus-lg me-1"></i> Ajouter une tâche
                    </button>
                </div>
            </div>

            <div id="planning-list-view">
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
                                <form method="POST" class="d-inline" onsubmit="return confirm('Supprimer cette tâche ?')">
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

            <div id="planning-calendar-view" style="display:none;">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center py-3 px-4">
                        <button class="btn btn-sm btn-outline-secondary rounded-3" onclick="calNav(-1)">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <h3 class="fw-bold fs-6 mb-0" id="cal-title">—</h3>
                        <button class="btn btn-sm btn-outline-secondary rounded-3" onclick="calNav(+1)">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                    <div class="card-body p-3">
                        <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:2px;margin-bottom:4px;">
                            <?php foreach (['Lun','Mar','Mer','Jeu','Ven','Sam','Dim'] as $d): ?>
                            <div class="text-center fw-bold small text-body-secondary py-1"><?= $d ?></div>
                            <?php endforeach; ?>
                        </div>
                        <div id="cal-grid" style="display:grid;grid-template-columns:repeat(7,1fr);gap:4px;min-height:380px;"></div>
                    </div>
                </div>
                <div id="cal-day-detail" class="mt-3" style="display:none;">
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-header bg-primary-subtle border-0 fw-bold rounded-top-3" id="cal-day-detail-title"></div>
                        <ul class="list-group list-group-flush rounded-bottom-3" id="cal-day-tasks"></ul>
                    </div>
                </div>
            </div>

            <div id="planning-gantt-view" style="display:none;">
                <?php if (empty($planningAvecDates)): ?>
                <div class="alert alert-info border-0 shadow-sm">
                    <i class="bi bi-info-circle me-2"></i>
                    Aucune tâche avec dates pour afficher le Gantt. Ajoutez des dates de début et fin à vos tâches.
                </div>
                <?php else: ?>
                <div class="card border-0 shadow-sm rounded-3 p-3">
                    <div id="gantt-container" class="overflow-auto" style="min-height:200px;"></div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="tab-pane fade" id="pane-materiel" role="tabpanel">

            <?php if ($totalMat > 0): ?>
            <ul class="row row-cols-3 g-3 list-unstyled mb-4">
                <?php foreach ([
                    ['label'=>'Matériel loué',   'val'=>$totalLoue,   'color'=>'warning', 'icon'=>'bi-arrow-repeat'],
                    ['label'=>'Matériel acheté',  'val'=>$totalAchete, 'color'=>'primary', 'icon'=>'bi-bag-check'],
                    ['label'=>'Budget total mat.','val'=>$totalMat,    'color'=>'dark',    'icon'=>'bi-cash-stack'],
                ] as $kpi): ?>
                <li class="col">
                    <article class="card border-0 shadow-sm rounded-3 text-center h-100">
                        <div class="card-body py-3">
                            <i class="bi <?= $kpi['icon'] ?> text-<?= $kpi['color'] ?> fs-4 mb-1 d-block"></i>
                            <p class="text-body-secondary small mb-1"><?= $kpi['label'] ?></p>
                            <p class="fw-bold fs-5 mb-0"><?= number_format((float)$kpi['val'],2,',',' ') ?> €</p>
                        </div>
                    </article>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>

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
                                <th class="ps-3">Nom</th><th>Catégorie</th><th>Qté</th>
                                <th>Fournisseur</th><th>Budget</th>
                                <th>Date In</th><th>Date Out</th><th>Commentaire</th><th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($materiel as $mat): ?>
                        <tr>
                            <td class="ps-3 fw-medium"><?= htmlspecialchars((string)$mat['nom'], ENT_QUOTES) ?></td>
                            <td>
                                <?php if (!empty($mat['categorie_achat'])): ?>
                                <span class="badge bg-<?= $mat['categorie_achat'] === 'loue' ? 'warning text-dark' : 'primary' ?>">
                                    <?= $mat['categorie_achat'] === 'loue' ? 'Loué' : 'Acheté' ?>
                                </span>
                                <?php else: ?>
                                <span class="text-body-secondary small">—</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars((string)$mat['quantite'], ENT_QUOTES) ?></td>
                            <td class="text-body-secondary small"><?= htmlspecialchars((string)($mat['fournisseur']??'—'), ENT_QUOTES) ?></td>
                            <td class="small fw-bold"><?= $mat['budget'] !== null ? number_format((float)$mat['budget'],2,',',' ').' €' : '—' ?></td>
                            <td class="small"><?= !empty($mat['date_in'])  ? date('d/m/Y', strtotime($mat['date_in']))  : '—' ?></td>
                            <td class="small"><?= !empty($mat['date_out']) ? date('d/m/Y', strtotime($mat['date_out'])) : '—' ?></td>
                            <td class="small text-body-secondary"><?= htmlspecialchars((string)($mat['commentaire']??''), ENT_QUOTES) ?></td>
                            <td class="text-end pe-3">
                                <button class="btn btn-sm btn-outline-secondary py-0 px-2 me-1"
                                        onclick="openMaterielEdit(<?= htmlspecialchars(json_encode($mat), ENT_QUOTES) ?>)">
                                    <i class="bi bi-pencil"></i></button>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce matériel ?')">
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

        <div class="tab-pane fade" id="pane-facturation" role="tabpanel">

            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div class="fw-bold text-body-secondary">
                    Total général :
                    <span class="text-primary fs-5 fw-bold ms-1">
                        <?= number_format($totalFacturation, 2, ',', ' ') ?> €
                    </span>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?= exportUrl('facturation', $eventId, $projetId, 'csv') ?>"
                       class="btn btn-sm btn-outline-success rounded-3" title="Exporter en Excel/CSV">
                        <i class="bi bi-file-earmark-excel me-1"></i>Excel
                    </a>
                    <a href="<?= exportUrl('facturation', $eventId, $projetId, 'pdf') ?>"
                       class="btn btn-sm btn-outline-danger rounded-3" title="Exporter en PDF"
                       target="_blank">
                        <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                    </a>
                    <button class="btn btn-info btn-sm fw-semibold shadow-sm text-white"
                            data-bs-toggle="modal" data-bs-target="#modalFacturationCreate">
                        <i class="bi bi-plus-lg me-1"></i> Ajouter une ligne
                    </button>
                </div>
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
                                <th class="text-center">Virement</th><th class="text-center">Fichier</th><th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($facturation as $fac):
                            $tot = (float)$fac['prix_unitaire'] * (float)$fac['quantite'];
                            // Afficher contact depuis contact lié ou champ texte
                            $contactDisplay = !empty($fac['contact_nom']) ? $fac['contact_nom'] : ($fac['contact'] ?? '—');
                            $telDisplay     = !empty($fac['contact_tel'])  ? $fac['contact_tel']  : ($fac['telephone'] ?? '—');
                            $mailDisplay    = !empty($fac['contact_mail']) ? $fac['contact_mail'] : ($fac['mail'] ?? '');
                        ?>
                        <tr>
                            <td class="ps-3 small"><?= htmlspecialchars((string)($fac['categorie']??''), ENT_QUOTES) ?></td>
                            <td class="fw-medium small"><?= htmlspecialchars((string)($fac['poste']??''), ENT_QUOTES) ?></td>
                            <td class="small"><?= htmlspecialchars((string)($fac['prestataire']??''), ENT_QUOTES) ?></td>
                            <td class="small"><?= htmlspecialchars($contactDisplay, ENT_QUOTES) ?></td>
                            <td class="small"><?= htmlspecialchars($telDisplay, ENT_QUOTES) ?></td>
                            <td class="small">
                                <?php if (!empty($mailDisplay)): ?>
                                <a href="mailto:<?= htmlspecialchars($mailDisplay, ENT_QUOTES) ?>" class="text-decoration-none text-primary">
                                    <?= htmlspecialchars($mailDisplay, ENT_QUOTES) ?></a>
                                <?php else: echo '—'; endif; ?>
                            </td>
                            <td class="text-end small"><?= number_format((float)$fac['prix_unitaire'],2,',',' ') ?> €</td>
                            <td class="text-end small"><?= htmlspecialchars((string)$fac['quantite'], ENT_QUOTES) ?></td>
                            <td class="text-end fw-bold"><?= number_format($tot,2,',',' ') ?> €</td>
                            <td class="text-center">
                                <form method="POST" class="d-inline">
                                    <?= opsForm($eventId, $projetId) ?>
                                    <input type="hidden" name="ops_action" value="facturation_toggle">
                                    <input type="hidden" name="ligne_id" value="<?= (int)$fac['id'] ?>">
                                    <input type="hidden" name="toggle_field" value="statut_devis">
                                    <button type="submit" class="btn btn-link p-0 border-0 bg-transparent"
                                            title="<?= $fac['statut_devis'] ? 'Cliquer pour annuler le devis' : 'Cliquer pour marquer devis reçu' ?>">
                                        <?= $fac['statut_devis']
                                            ? '<i class="bi bi-check-circle-fill text-success fs-5"></i>'
                                            : '<i class="bi bi-circle text-body-secondary fs-5"></i>' ?>
                                    </button>
                                </form>
                            </td>
                            <td class="text-center">
                                <form method="POST" class="d-inline">
                                    <?= opsForm($eventId, $projetId) ?>
                                    <input type="hidden" name="ops_action" value="facturation_toggle">
                                    <input type="hidden" name="ligne_id" value="<?= (int)$fac['id'] ?>">
                                    <input type="hidden" name="toggle_field" value="statut_facture">
                                    <button type="submit" class="btn btn-link p-0 border-0 bg-transparent"
                                            title="<?= $fac['statut_facture'] ? 'Cliquer pour annuler la facture' : 'Cliquer pour marquer facture reçue' ?>">
                                        <?= $fac['statut_facture']
                                            ? '<i class="bi bi-check-circle-fill text-success fs-5"></i>'
                                            : '<i class="bi bi-circle text-body-secondary fs-5"></i>' ?>
                                    </button>
                                </form>
                            </td>
                            <td class="text-center">
                                <form method="POST" class="d-inline">
                                    <?= opsForm($eventId, $projetId) ?>
                                    <input type="hidden" name="ops_action" value="facturation_toggle">
                                    <input type="hidden" name="ligne_id" value="<?= (int)$fac['id'] ?>">
                                    <input type="hidden" name="toggle_field" value="statut_virement">
                                    <button type="submit" class="btn btn-link p-0 border-0 bg-transparent"
                                            title="<?= $fac['statut_virement'] ? 'Cliquer pour annuler le virement' : 'Cliquer pour marquer virement effectué' ?>">
                                        <?= $fac['statut_virement']
                                            ? '<i class="bi bi-check-circle-fill text-success fs-5"></i>'
                                            : '<i class="bi bi-circle text-body-secondary fs-5"></i>' ?>
                                    </button>
                                </form>
                            </td>
                            <td class="text-center">
                                <?php if (!empty($fac['fichier'])): ?>
                                <a href="/<?= htmlspecialchars($fac['fichier'], ENT_QUOTES) ?>" target="_blank"
                                   class="btn btn-sm btn-outline-secondary py-0 px-1" title="Voir le fichier">
                                    <i class="bi bi-paperclip"></i>
                                </a>
                                <?php else: echo '<span class="text-body-secondary small">—</span>'; endif; ?>
                            </td>
                            <td class="text-end pe-3">
                                <button class="btn btn-sm btn-outline-secondary py-0 px-2 me-1"
                                        onclick="openFacturationEdit(<?= htmlspecialchars(json_encode($fac), ENT_QUOTES) ?>)">
                                    <i class="bi bi-pencil"></i></button>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Supprimer cette ligne ?')">
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
                                <td colspan="5"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="tab-pane fade" id="pane-preprod" role="tabpanel">

            <?php if ($eventId > 0 && $eventData): ?>
            <div class="row g-4 mb-4">
                <?php
                $phases = [
                    ['deb'=>'date_preprod_debut',   'fin'=>'date_preprod_fin',   'label'=>'Pré-production',        'color'=>'warning', 'icon'=>'bi-hammer'],
                    ['deb'=>'date_prod_debut',      'fin'=>'date_prod_fin',      'label'=>'Production/Installation','color'=>'primary', 'icon'=>'bi-gear-fill'],
                    ['deb'=>'date_exploit_debut',   'fin'=>'date_exploit_fin',   'label'=>'Exploitation/Événement', 'color'=>'success', 'icon'=>'bi-play-circle-fill'],
                    ['deb'=>'date_demontage_debut', 'fin'=>'date_demontage_fin', 'label'=>'Démontage',              'color'=>'danger',  'icon'=>'bi-trash3-fill'],
                ];
                foreach ($phases as $ph):
                    $deb = $eventData[$ph['deb']] ?? null;
                    $fin = $eventData[$ph['fin']] ?? null;
                ?>
                <div class="col-md-6 col-xl-3">
                    <article class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-header bg-<?= $ph['color'] ?>-subtle border-0 rounded-top-3">
                            <span class="fw-bold text-<?= $ph['color'] ?>">
                                <i class="bi <?= $ph['icon'] ?> me-2"></i><?= $ph['label'] ?>
                            </span>
                        </div>
                        <div class="card-body text-center py-3">
                            <?php if ($deb || $fin): ?>
                            <p class="mb-1 fw-semibold">
                                <?= $deb ? date('d/m/Y', strtotime($deb)) : '?' ?>
                                <i class="bi bi-arrow-right mx-1 small"></i>
                                <?= $fin ? date('d/m/Y', strtotime($fin)) : '?' ?>
                            </p>
                            <?php if ($deb && $fin): ?>
                            <?php
                                $nbJours = (int)ceil((strtotime($fin) - strtotime($deb)) / 86400) + 1;
                            ?>
                            <span class="badge bg-<?= $ph['color'] ?> bg-opacity-75"><?= $nbJours ?> jour<?= $nbJours > 1 ? 's' : '' ?></span>
                            <?php endif; ?>
                            <?php else: ?>
                            <p class="text-body-secondary small mb-0"><i class="bi bi-dash-circle me-1"></i>Non défini</p>
                            <?php endif; ?>
                        </div>
                    </article>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="alert alert-light border shadow-sm d-flex justify-content-between align-items-center">
                <span class="small text-body-secondary"><i class="bi bi-info-circle me-2"></i>Modifiez les dates de phases depuis la fiche événement.</span>
                <a href="/gerer_event?id=<?= $eventId ?>" class="btn btn-sm btn-outline-primary rounded-3">
                    <i class="bi bi-pencil me-1"></i>Modifier l'événement
                </a>
            </div>

            <div class="alert alert-success border-0 shadow-sm d-flex align-items-center gap-3 mb-3 flex-wrap">
                <i class="bi bi-arrow-repeat text-success fs-4 flex-shrink-0"></i>
                <div class="flex-grow-1">
                    <strong>Synchronisation automatique avec le planning activée.</strong>
                    <span class="text-body-secondary small ms-1">
                        Les phases sont synchronisées à chaque modification de l'événement.
                        Tu peux aussi forcer la synchronisation manuellement ci-dessous.
                    </span>
                </div>
                <div class="d-flex gap-2 flex-shrink-0">
                    <form method="POST" class="d-inline">
                        <?= opsForm($eventId, $projetId) ?>
                        <input type="hidden" name="ops_action" value="preprod_sync">
                        <button type="submit" class="btn btn-success btn-sm rounded-3 fw-semibold">
                            <i class="bi bi-arrow-repeat me-1"></i>Synchroniser maintenant
                        </button>
                    </form>
                    <a href="#pane-planning"
                       onclick="document.querySelector('[data-bs-target=\'#pane-planning\']').click(); return false;"
                       class="btn btn-sm btn-outline-success rounded-3">
                        <i class="bi bi-calendar3 me-1"></i>Voir le planning
                    </a>
                </div>
            </div>

            <?php
            $allDates = [];
            foreach ($phases as $ph) {
                if (!empty($eventData[$ph['deb']])) $allDates[] = strtotime($eventData[$ph['deb']]);
                if (!empty($eventData[$ph['fin']])) $allDates[] = strtotime($eventData[$ph['fin']]);
            }
            if (count($allDates) >= 2):
                $minTs = min($allDates);
                $maxTs = max($allDates);
                $totalDays = max(1, ($maxTs - $minTs) / 86400);
            ?>
            <div class="card border-0 shadow-sm rounded-3 mt-4">
                <div class="card-header fw-bold border-0">
                    <i class="bi bi-bar-chart-steps me-2 text-primary"></i>Timeline des phases
                </div>
                <div class="card-body">
                    <?php foreach ($phases as $ph):
                        $deb = !empty($eventData[$ph['deb']]) ? strtotime($eventData[$ph['deb']]) : null;
                        $fin = !empty($eventData[$ph['fin']]) ? strtotime($eventData[$ph['fin']]) : null;
                        if (!$deb && !$fin) continue;
                        $deb = $deb ?? $minTs;
                        $fin = $fin ?? $maxTs;
                        $left  = round(($deb - $minTs) / ($totalDays * 86400) * 100);
                        $width = max(2, round(($fin - $deb) / ($totalDays * 86400) * 100));
                    ?>
                    <div class="d-flex align-items-center mb-2">
                        <div style="width:180px;" class="small fw-medium text-truncate me-2">
                            <i class="bi <?= $ph['icon'] ?> me-1 text-<?= $ph['color'] ?>"></i><?= $ph['label'] ?>
                        </div>
                        <div class="flex-grow-1 position-relative" style="height:28px;background:var(--bs-secondary-bg);border-radius:6px;overflow:hidden;">
                            <div class="position-absolute h-100 bg-<?= $ph['color'] ?> bg-opacity-75 d-flex align-items-center justify-content-center"
                                 style="left:<?= $left ?>%;width:<?= $width ?>%;border-radius:4px;">
                                <small class="text-white fw-bold text-truncate px-1" style="font-size:.7rem;">
                                    <?= date('d/m', $deb) ?>→<?= date('d/m', $fin) ?>
                                </small>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php else: ?>
            <div class="alert alert-info border-0 shadow-sm">
                <i class="bi bi-info-circle me-2"></i>
                Sélectionne un événement pour voir les phases de production.
            </div>
            <?php endif; ?>
        </div>

        <div class="tab-pane fade" id="pane-contacts-ops" role="tabpanel">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <p class="text-body-secondary small mb-0">
                    Contacts et prestataires liés à <?= $eventId > 0 ? 'cet événement' : 'ce projet' ?>.
                </p>
                <a href="/annuaire" class="btn btn-sm btn-outline-secondary rounded-3">
                    <i class="bi bi-person-plus me-1"></i>Gérer les contacts
                </a>
            </div>

            <?php if (empty($contactsLies)): ?>
            <div class="text-center py-5 text-body-secondary">
                <i class="bi bi-person-lines-fill fs-2 d-block mb-2 opacity-50"></i>
                <p>Aucun contact lié. Rendez-vous dans l'<a href="/annuaire">Annuaire</a> pour lier des contacts.</p>
            </div>
            <?php else: ?>
            <div class="card border-0 shadow-sm rounded-3">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark small">
                            <tr>
                                <th scope="col" class="ps-3">Nom</th>
                                <th scope="col">Rôle / Poste</th>
                                <th scope="col">Société</th>
                                <th scope="col">Téléphone</th>
                                <th scope="col">Email</th>
                                <th scope="col">Note</th>
                                <th scope="col" class="text-end pe-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($contactsLies as $cl): ?>
                                <tr>
                                    <td class="ps-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                                                 style="width: 32px; height: 32px; font-size: .8rem;">
                                                <?= mb_strtoupper(mb_substr($cl['nom'], 0, 1)) ?>
                                            </div>
                                            <span class="fw-bold text-dark small">
                                                <?= htmlspecialchars((string)$cl['nom'], ENT_QUOTES) ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="small">
                                        <?php if (!empty($cl['lien_role'])): ?>
                                            <span class="badge bg-info-subtle text-info rounded-pill">
                                                <?= htmlspecialchars((string)$cl['lien_role'], ENT_QUOTES) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="small text-body-secondary">
                                        <?= !empty($cl['societe']) ? htmlspecialchars((string)$cl['societe'], ENT_QUOTES) : '—' ?>
                                    </td>
                                    <td class="small">
                                        <?php if (!empty($cl['telephone'])): ?>
                                            <a href="tel:<?= htmlspecialchars($cl['telephone'], ENT_QUOTES) ?>" class="text-decoration-none text-secondary">
                                                <i class="bi bi-telephone me-1"></i><?= htmlspecialchars($cl['telephone'], ENT_QUOTES) ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="small">
                                        <?php if (!empty($cl['mail'])): ?>
                                            <a href="mailto:<?= htmlspecialchars($cl['mail'], ENT_QUOTES) ?>" class="text-decoration-none">
                                                <i class="bi bi-envelope me-1"></i><?= htmlspecialchars($cl['mail'], ENT_QUOTES) ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="small text-body-secondary fst-italic">
                                        <?= !empty($cl['lien_note']) ? htmlspecialchars((string)$cl['lien_note'], ENT_QUOTES) : '—' ?>
                                    </td>
                                    <td class="text-end pe-3">
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Détacher ce contact ?')">
                                            <?= opsForm($eventId, $projetId) ?>
                                            <input type="hidden" name="ops_action" value="contact_detach">
                                            <input type="hidden" name="lien_id"   value="<?= (int)$cl['lien_id'] ?>">
                                            <input type="hidden" name="lien_type" value="<?= $eventId > 0 ? 'event' : 'projet' ?>">
                                            <button class="btn btn-sm btn-outline-danger py-0 px-2" title="Détacher">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
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

    </div><?php endif; // contexte sélectionné ?>

</div><?php
$statuts_planning_opts = ['wip','en_cours','valide','maj','devis','visuels','bat','prod','annule'];
$statuts_labels        = ['wip'=>'WIP','en_cours'=>'En cours','valide'=>'Validé','maj'=>'Maj',
                          'devis'=>'Devis','visuels'=>'Visuels','bat'=>'BAT','prod'=>'Prod','annule'=>'Annulé'];
?>

<div class="modal fade" id="modalBudgetCreate" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold"><i class="bi bi-bar-chart-fill me-2 text-success"></i>Nouvelle ligne de budget</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
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
              <input type="text" name="sous_categorie" class="form-control rounded-3">
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
              <label class="form-label fw-semibold">Comparatif (€)</label>
              <input type="number" step="0.01" name="comparatif" class="form-control rounded-3" value="0">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold"><i class="bi bi-truck me-1"></i>Fournisseur</label>
              <input type="text" name="fournisseur" class="form-control rounded-3" placeholder="Nom du fournisseur">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold"><i class="bi bi-star me-1 text-warning"></i>Sponsor</label>
              <input type="text" name="sponsor" class="form-control rounded-3" placeholder="Nom du sponsor">
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

<div class="modal fade" id="modalBudgetEdit" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold"><i class="bi bi-pencil me-2 text-primary"></i>Modifier la ligne budget</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
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
              <label class="form-label fw-semibold">Comparatif (€)</label>
              <input type="number" step="0.01" name="comparatif" id="be-comp" class="form-control rounded-3">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Fournisseur</label>
              <input type="text" name="fournisseur" id="be-four" class="form-control rounded-3">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Sponsor</label>
              <input type="text" name="sponsor" id="be-spon" class="form-control rounded-3">
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

<div class="modal fade" id="modalPlanningCreate" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold"><i class="bi bi-calendar-plus me-2 text-primary"></i>Nouvelle tâche planning</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
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

<div class="modal fade" id="modalPlanningEdit" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold"><i class="bi bi-pencil me-2 text-primary"></i>Modifier la tâche</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
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

<div class="modal fade" id="modalMaterielCreate" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold"><i class="bi bi-box-seam me-2 text-warning"></i>Nouveau matériel</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form method="POST">
          <?= opsForm($eventId, $projetId) ?>
          <input type="hidden" name="ops_action" value="materiel_create">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Nom <span class="text-danger">*</span></label>
              <input type="text" name="nom" class="form-control rounded-3" required>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Quantité</label>
              <input type="number" step="0.5" name="quantite" class="form-control rounded-3" value="1">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Catégorie</label>
              <select name="categorie_achat" class="form-select rounded-3">
                <option value="">— Non défini —</option>
                <option value="loue">Loué</option>
                <option value="achete">Acheté</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Fournisseur</label>
              <input type="text" name="fournisseur" class="form-control rounded-3">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Budget (€)</label>
              <input type="number" step="0.01" name="budget" class="form-control rounded-3" placeholder="0.00">
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

<div class="modal fade" id="modalMaterielEdit" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold"><i class="bi bi-pencil me-2 text-primary"></i>Modifier le matériel</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form method="POST">
          <?= opsForm($eventId, $projetId) ?>
          <input type="hidden" name="ops_action" value="materiel_update">
          <input type="hidden" name="ligne_id" id="me-id">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Nom</label>
              <input type="text" name="nom" id="me-nom" class="form-control rounded-3" required>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Quantité</label>
              <input type="number" step="0.5" name="quantite" id="me-qte" class="form-control rounded-3">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Catégorie</label>
              <select name="categorie_achat" id="me-cat" class="form-select rounded-3">
                <option value="">— Non défini —</option>
                <option value="loue">Loué</option>
                <option value="achete">Acheté</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Fournisseur</label>
              <input type="text" name="fournisseur" id="me-four" class="form-control rounded-3">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Budget (€)</label>
              <input type="number" step="0.01" name="budget" id="me-budget" class="form-control rounded-3">
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

<div class="modal fade" id="modalFacturationCreate" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold"><i class="bi bi-receipt me-2 text-info"></i>Nouvelle ligne de facturation</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form method="POST" enctype="multipart/form-data">
          <?= opsForm($eventId, $projetId) ?>
          <input type="hidden" name="ops_action" value="facturation_create">
          <div class="row g-3">

            <div class="col-12">
              <label class="form-label fw-semibold"><i class="bi bi-person-lines-fill me-1 text-primary"></i>Contact existant (optionnel)</label>
              <select name="contact_id" id="fc-contact-select" class="form-select rounded-3" onchange="fillContactFromSelect(this,'fc')">
                <option value="">— Saisir manuellement —</option>
                <?php foreach ($contacts as $ct): ?>
                <option value="<?= (int)$ct['id'] ?>"
                        data-nom="<?= htmlspecialchars((string)($ct['nom']??''), ENT_QUOTES) ?>"
                        data-tel="<?= htmlspecialchars((string)($ct['telephone']??''), ENT_QUOTES) ?>"
                        data-mail="<?= htmlspecialchars((string)($ct['mail']??''), ENT_QUOTES) ?>">
                    <?= htmlspecialchars((string)$ct['nom'], ENT_QUOTES) ?>
                    <?= !empty($ct['type']) ? ' ('.$ct['type'].')' : '' ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-4"><label class="form-label fw-semibold">Catégorie</label>
              <input type="text" name="categorie" class="form-control rounded-3" placeholder="ex: Hébergement"></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Poste</label>
              <input type="text" name="poste" class="form-control rounded-3" placeholder="ex: Logement"></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Prestataire</label>
              <input type="text" name="prestataire" class="form-control rounded-3"></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Contact</label>
              <input type="text" name="contact" id="fc-contact" class="form-control rounded-3"></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Téléphone</label>
              <input type="text" name="telephone" id="fc-tel" class="form-control rounded-3"></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Mail</label>
              <input type="email" name="mail" id="fc-mail" class="form-control rounded-3"></div>
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
            <div class="col-md-8"><label class="form-label fw-semibold">Note</label>
              <input type="text" name="note" class="form-control rounded-3"></div>
            <div class="col-md-4">
              <label class="form-label fw-semibold"><i class="bi bi-paperclip me-1"></i>Fichier joint</label>
              <input type="file" name="fichier_upload" class="form-control rounded-3"
                     accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx">
              <small class="text-body-secondary">PDF, image, Word, Excel</small>
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

<div class="modal fade" id="modalFacturationEdit" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold"><i class="bi bi-pencil me-2 text-primary"></i>Modifier la facturation</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form method="POST" enctype="multipart/form-data">
          <?= opsForm($eventId, $projetId) ?>
          <input type="hidden" name="ops_action" value="facturation_update">
          <input type="hidden" name="ligne_id" id="fe-id">
          <input type="hidden" name="fichier_existing" id="fe-fichier-existing">
          <div class="row g-3">

            <div class="col-12">
              <label class="form-label fw-semibold"><i class="bi bi-person-lines-fill me-1 text-primary"></i>Contact existant</label>
              <select name="contact_id" id="fe-contact-select" class="form-select rounded-3" onchange="fillContactFromSelect(this,'fe')">
                <option value="">— Saisir manuellement —</option>
                <?php foreach ($contacts as $ct): ?>
                <option value="<?= (int)$ct['id'] ?>"
                        data-nom="<?= htmlspecialchars((string)($ct['nom']??''), ENT_QUOTES) ?>"
                        data-tel="<?= htmlspecialchars((string)($ct['telephone']??''), ENT_QUOTES) ?>"
                        data-mail="<?= htmlspecialchars((string)($ct['mail']??''), ENT_QUOTES) ?>">
                    <?= htmlspecialchars((string)$ct['nom'], ENT_QUOTES) ?>
                    <?= !empty($ct['type']) ? ' ('.$ct['type'].')' : '' ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>

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
            <div class="col-md-8"><label class="form-label fw-semibold">Note</label><input type="text" name="note" id="fe-note" class="form-control rounded-3"></div>
            <div class="col-md-4">
              <label class="form-label fw-semibold"><i class="bi bi-paperclip me-1"></i>Fichier joint</label>
              <div id="fe-fichier-current" class="mb-1 small text-body-secondary"></div>
              <input type="file" name="fichier_upload" class="form-control rounded-3"
                     accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx">
              <small class="text-body-secondary">Laisser vide pour conserver le fichier existant</small>
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

<?php if ($eventId > 0): ?>
<div class="modal fade" id="modalDriveLinks" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold"><i class="bi bi-google me-2 text-danger"></i>Liens Google Drive / Maps</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form method="POST">
          <?= opsForm($eventId, $projetId) ?>
          <input type="hidden" name="ops_action" value="event_drive_update">
          <div class="mb-3">
            <label class="form-label fw-semibold"><i class="bi bi-cloud me-1 text-primary"></i>Google Drive (dossier)</label>
            <input type="url" name="drive_url" class="form-control rounded-3"
                   value="<?= htmlspecialchars((string)($eventData['drive_url'] ?? ''), ENT_QUOTES) ?>"
                   placeholder="https://drive.google.com/drive/folders/...">
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold"><i class="bi bi-file-earmark-text me-1 text-success"></i>Google Doc / Sheet</label>
            <input type="url" name="drive_doc_url" class="form-control rounded-3"
                   value="<?= htmlspecialchars((string)($eventData['drive_doc_url'] ?? ''), ENT_QUOTES) ?>"
                   placeholder="https://docs.google.com/...">
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold"><i class="bi bi-geo-alt me-1 text-danger"></i>Google My Maps</label>
            <input type="url" name="maps_url" class="form-control rounded-3"
                   value="<?= htmlspecialchars((string)($eventData['maps_url'] ?? ''), ENT_QUOTES) ?>"
                   placeholder="https://www.google.com/maps/...">
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Annuler</button>
            <button type="submit" class="btn btn-danger rounded-3 fw-semibold"><i class="bi bi-save me-1"></i>Enregistrer</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
window.OPS_PLANNING_DATA = <?= json_encode(array_values($planningAvecDates), JSON_HEX_TAG) ?>;
window.OPS_PLANNING_STATUTS = <?= json_encode($planningStatuts, JSON_HEX_TAG) ?>;
</script>