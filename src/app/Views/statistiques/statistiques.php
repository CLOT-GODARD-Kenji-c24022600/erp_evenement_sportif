<?php

/**
 * YES – Your Event Solution
 *
 * Vue : Statistiques & Reporting
 *
 * @file statistiques.php
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.1
 * @since 2026
 */

declare(strict_types=1);

$fmt = fn(float $n): string => number_format($n, 2, ',', ' ') . ' €';
?>

<div class="container-fluid py-4 px-4" id="statistiques-page">

    <!-- ── En-tête ─────────────────────────────────────────── -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h3 fw-bold mb-0">
                <i class="bi bi-bar-chart-line-fill text-primary me-2" aria-hidden="true"></i>
                Statistiques & Reporting
            </h1>
            <p class="text-muted small mb-0 mt-1">Vue d'ensemble de l'activité YES</p>
        </div>
        <button id="btn-refresh-stats" class="btn btn-outline-primary btn-sm rounded-3 d-flex align-items-center gap-2">
            <i class="bi bi-arrow-repeat" aria-hidden="true"></i>Actualiser
        </button>
    </div>

    <!-- ── KPIs ─────────────────────────────────────────────── -->
    <div class="row g-3 mb-4">
        <?php
        $kpiCards = [
            ['icon' => 'bi-calendar-event-fill', 'color' => 'primary',   'label' => 'Événements',        'key' => 'nb_evenements',  'type' => 'int'],
            ['icon' => 'bi-kanban-fill',          'color' => 'info',      'label' => 'Projets',           'key' => 'nb_projets',     'type' => 'int'],
            ['icon' => 'bi-people-fill',          'color' => 'success',   'label' => 'Contacts',          'key' => 'nb_contacts',    'type' => 'int'],
            ['icon' => 'bi-receipt',              'color' => 'warning',   'label' => 'Total facturé',     'key' => 'total_facture',  'type' => 'money'],
            ['icon' => 'bi-graph-up-arrow',       'color' => 'success',   'label' => 'Produits budget',   'key' => 'total_produits', 'type' => 'money'],
            ['icon' => 'bi-graph-down-arrow',     'color' => 'danger',    'label' => 'Charges budget',    'key' => 'total_charges',  'type' => 'money'],
            ['icon' => 'bi-check2-circle',        'color' => 'success',   'label' => 'Tâches terminées',  'key' => 'taux_todos',     'type' => 'pct'],
            ['icon' => 'bi-person-check-fill',    'color' => 'secondary', 'label' => 'Utilisateurs actifs','key' => 'nb_users',      'type' => 'int'],
        ];
        foreach ($kpiCards as $c):
            $val     = $kpis[$c['key']] ?? 0;
            $display = match ($c['type']) {
                'money' => $fmt((float) $val),
                'pct'   => $val . '%',
                default => $val,
            };
        ?>
        <div class="col-6 col-md-4 col-xl-3">
            <div class="card border-0 shadow-sm h-100 rounded-3 stats-kpi-card">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0 stats-kpi-icon bg-<?= $c['color'] ?>-subtle">
                        <i class="bi <?= $c['icon'] ?> fs-5 text-<?= $c['color'] ?>" aria-hidden="true"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="text-muted small"><?= htmlspecialchars($c['label'], ENT_QUOTES) ?></div>
                        <div class="fw-bold fs-5 text-<?= $c['color'] ?>"><?= $display ?></div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- ── Ligne 1 : Événements par mois + Facturation par mois ── -->
    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-transparent border-0 pt-3 pb-0 px-4">
                    <h2 class="h6 fw-bold mb-0">
                        <i class="bi bi-bar-chart-fill text-primary me-2" aria-hidden="true"></i>
                        Événements par mois
                        <span class="text-muted fw-normal small">(12 derniers mois)</span>
                    </h2>
                </div>
                <div class="card-body px-3 pb-3">
                    <canvas id="chart-events-mois" height="220" aria-label="Graphique événements par mois"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-transparent border-0 pt-3 pb-0 px-4">
                    <h2 class="h6 fw-bold mb-0">
                        <i class="bi bi-graph-up text-success me-2" aria-hidden="true"></i>
                        Facturation par mois
                        <span class="text-muted fw-normal small">(12 derniers mois)</span>
                    </h2>
                </div>
                <div class="card-body px-3 pb-3">
                    <canvas id="chart-fact-mois" height="220" aria-label="Graphique facturation par mois"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Ligne 2 : Budget événements + Donuts complétion ── -->
    <div class="row g-3 mb-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-transparent border-0 pt-3 pb-0 px-4">
                    <h2 class="h6 fw-bold mb-0">
                        <i class="bi bi-cash-stack text-warning me-2" aria-hidden="true"></i>
                        Budget par événement
                        <span class="text-muted fw-normal small">(top 8 — prévisionnel)</span>
                    </h2>
                </div>
                <div class="card-body px-3 pb-3">
                    <canvas id="chart-budget" height="260" aria-label="Graphique budget par événement"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-transparent border-0 pt-3 pb-0 px-4">
                    <h2 class="h6 fw-bold mb-0">
                        <i class="bi bi-check2-all text-success me-2" aria-hidden="true"></i>
                        Taux de complétion
                    </h2>
                </div>
                <div class="card-body d-flex gap-4 align-items-center justify-content-around flex-wrap pb-3">
                    <div class="text-center">
                        <canvas id="chart-donut-todo" width="130" height="130" aria-label="Complétion des tâches"></canvas>
                        <p class="small text-muted mt-2 fw-semibold mb-0">Tâches</p>
                    </div>
                    <div class="text-center">
                        <canvas id="chart-donut-planning" width="130" height="130" aria-label="Complétion du planning"></canvas>
                        <p class="small text-muted mt-2 fw-semibold mb-0">Planning</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Ligne 3 : Top prestataires graphique + tableau ── -->
    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-transparent border-0 pt-3 pb-0 px-4">
                    <h2 class="h6 fw-bold mb-0">
                        <i class="bi bi-trophy-fill text-warning me-2" aria-hidden="true"></i>
                        Top 5 prestataires <span class="text-muted fw-normal small">(montant facturé)</span>
                    </h2>
                </div>
                <div class="card-body px-3 pb-3">
                    <canvas id="chart-prestataires" height="200" aria-label="Graphique top prestataires"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-transparent border-0 pt-3 pb-0 px-4">
                    <h2 class="h6 fw-bold mb-0">
                        <i class="bi bi-list-ol text-info me-2" aria-hidden="true"></i>
                        Détail top prestataires
                    </h2>
                </div>
                <div class="card-body px-0 pb-2">
                    <table class="table table-sm table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4" style="width:40px">#</th>
                                <th>Prestataire</th>
                                <th class="text-end pe-4">Montant</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($topPrestataires)): ?>
                                <?php foreach ($topPrestataires as $i => $p): ?>
                                <tr>
                                    <td class="ps-4 text-muted fw-semibold"><?= $i + 1 ?></td>
                                    <td class="fw-semibold"><?= htmlspecialchars((string) $p['prestataire'], ENT_QUOTES) ?></td>
                                    <td class="text-end pe-4 text-success fw-bold"><?= $fmt((float) $p['total']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4 small">
                                        <i class="bi bi-inbox me-2" aria-hidden="true"></i>Aucune donnée de facturation
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div><!-- /statistiques-page -->

<!-- ── Données JSON pour le JS (zéro requête AJAX au premier chargement) ── -->
<script id="stats-data" type="application/json"><?= json_encode([
    'evenementsParMois' => $evenementsParMois ?? [],
    'budgetParEvent'    => $budgetParEvent    ?? [],
    'tauxCompletion'    => $tauxCompletion    ?? [],
    'topPrestataires'   => $topPrestataires  ?? [],
    'factParMois'       => $factParMois      ?? [],
    'kpis'              => $kpis             ?? [],
], JSON_UNESCAPED_UNICODE) ?></script>