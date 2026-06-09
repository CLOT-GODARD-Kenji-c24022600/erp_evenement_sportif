<?php

/**
 * YES – Your Event Solution
 * 
 * @file historique.php
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 2.1
 * @since 2026
 */

declare(strict_types=1);

$actionLabels = [
    'create'    => ['label' => 'Création',    'class' => 'bg-success'],
    'update'    => ['label' => 'Modification', 'class' => 'bg-warning text-dark'],
    'delete'    => ['label' => 'Suppression',  'class' => 'bg-danger'],
    'duplicate' => ['label' => 'Duplication',  'class' => 'bg-info text-dark'],
    'login'     => ['label' => 'Connexion',    'class' => 'bg-primary'],
    'logout'    => ['label' => 'Déconnexion',  'class' => 'bg-secondary'],
    'approve'   => ['label' => 'Approbation',  'class' => 'bg-success'],
    'reject'    => ['label' => 'Rejet',        'class' => 'bg-danger'],
];

$entiteLabels = [
    'evenement'   => '📅 Événement',
    'projet'      => '📁 Projet',
    'budget'      => '💰 Budget',
    'facture'     => '🧾 Facture',
    'todo'        => '✅ Tâche',
    'planning'    => '🗓 Planning',
    'materiel'    => '🔧 Matériel',
    'utilisateur' => '👤 Utilisateur',
    'contact'     => '📞 Contact',
];
?>

<div class="container-fluid py-4 px-3 px-md-4">

    <!-- En-tête -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div>
            <h1 class="h4 fw-bold mb-0">
                <i class="bi bi-clock-history text-primary me-2"></i>Historique des actions
            </h1>
            <p class="text-muted small mb-0 mt-1">Traçabilité complète des opérations effectuées dans YES</p>
        </div>
        <span class="badge bg-secondary rounded-pill fs-6">
            <?= count($logs) ?> entrée<?= count($logs) > 1 ? 's' : '' ?>
        </span>
    </div>

    <!-- Filtres -->
    <form method="GET" action="/historique" class="card border-0 shadow-sm mb-4 p-3">
        <div class="row g-3 align-items-end">

            <div class="col-12 col-sm-6 col-lg-3">
                <label class="form-label small fw-semibold text-muted text-uppercase mb-1">
                    <i class="bi bi-tag me-1"></i>Type d'entité
                </label>
                <select name="entite" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">— Toutes les entités —</option>
                    <?php foreach ($entites as $e): ?>
                        <option value="<?= htmlspecialchars($e, ENT_QUOTES) ?>"
                            <?= $entite === $e ? 'selected' : '' ?>>
                            <?= htmlspecialchars($entiteLabels[$e] ?? ucfirst($e), ENT_QUOTES) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-12 col-sm-6 col-lg-3">
                <label class="form-label small fw-semibold text-muted text-uppercase mb-1">
                    <i class="bi bi-person me-1"></i>Utilisateur
                </label>
                <select name="user_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="0">— Tous les utilisateurs —</option>
                    <?php foreach ($users as $u): ?>
                        <option value="<?= (int)$u['user_id'] ?>"
                            <?= $userId === (int)$u['user_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($u['user_nom'], ENT_QUOTES) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-12 col-sm-6 col-lg-2">
                <label class="form-label small fw-semibold text-muted text-uppercase mb-1">
                    <i class="bi bi-calendar me-1"></i>Depuis
                </label>
                <input type="date" name="date_from" class="form-control form-control-sm"
                       value="<?= htmlspecialchars($dateFrom, ENT_QUOTES) ?>"
                       onchange="this.form.submit()">
            </div>

            <div class="col-12 col-sm-6 col-lg-2">
                <label class="form-label small fw-semibold text-muted text-uppercase mb-1">
                    <i class="bi bi-calendar-check me-1"></i>Jusqu'au
                </label>
                <input type="date" name="date_to" class="form-control form-control-sm"
                       value="<?= htmlspecialchars($dateTo, ENT_QUOTES) ?>"
                       onchange="this.form.submit()">
            </div>

            <div class="col-12 col-lg-2 d-flex align-items-end">
                <?php if ($entite || $userId || $dateFrom || $dateTo): ?>
                    <a href="/historique" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="bi bi-x-circle me-1"></i>Effacer les filtres
                    </a>
                <?php else: ?>
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-funnel me-1"></i>Filtrer
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </form>

    <!-- Tableau -->
    <?php if (empty($logs)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5 text-muted">
                <i class="bi bi-inbox fs-1 d-block mb-3 opacity-50"></i>
                <p class="mb-0">Aucune entrée d'historique pour ces critères.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle mb-0" id="historiqueTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width:155px;">Date &amp; heure</th>
                            <th style="width:120px;">Utilisateur</th>
                            <th style="width:105px;">Action</th>
                            <th style="width:115px;">Entité</th>
                            <th>Objet</th>
                            <th class="text-center" style="width:80px;">Détails</th>
                            <th class="pe-3 d-none d-xl-table-cell" style="width:105px;">IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log):
                            $actionInfo  = $actionLabels[$log['action']] ?? ['label' => ucfirst($log['action']), 'class' => 'bg-secondary'];
                            $entiteLabel = $entiteLabels[$log['entite']] ?? ucfirst($log['entite']);
                            $details     = [];
                            if (!empty($log['details'])) {
                                $details = json_decode($log['details'], true) ?? [];
                            }
                            $hasDetails = !empty($details);
                            $logId      = (int) $log['id'];
                        ?>
                        <tr>
                            <td class="ps-3 text-muted small">
                                <i class="bi bi-clock me-1 opacity-50"></i>
                                <?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 fw-bold"
                                          style="width:26px;height:26px;font-size:0.7rem;">
                                        <?= strtoupper(substr($log['user_nom'] ?? '?', 0, 1)) ?>
                                    </span>
                                    <span class="small"><?= htmlspecialchars($log['user_nom'] ?? 'Système', ENT_QUOTES) ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="badge <?= $actionInfo['class'] ?> rounded-pill">
                                    <?= $actionInfo['label'] ?>
                                </span>
                            </td>
                            <td class="small"><?= htmlspecialchars($entiteLabel, ENT_QUOTES) ?></td>
                            <td>
                                <span class="fw-semibold small"><?= htmlspecialchars($log['entite_label'] ?? '', ENT_QUOTES) ?></span>
                                <span class="text-muted small ms-1">#<?= (int)$log['entite_id'] ?></span>
                            </td>
                            <td class="text-center">
                                <?php if ($hasDetails): ?>
                                    <button type="button"
                                            class="btn btn-outline-secondary btn-xs py-0 px-2"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#detail-<?= $logId ?>"
                                            aria-expanded="false">
                                        <i class="bi bi-chevron-down" style="font-size:0.7rem;"></i>
                                    </button>
                                <?php else: ?>
                                    <span class="text-muted" style="font-size:0.75rem;">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="pe-3 small text-muted d-none d-xl-table-cell font-monospace">
                                <?= htmlspecialchars($log['ip'] ?? '', ENT_QUOTES) ?>
                            </td>
                        </tr>

                        <?php if ($hasDetails): ?>
                        <tr class="collapse" id="detail-<?= $logId ?>">
                            <td colspan="7" class="bg-light p-0">
                                <div class="p-3">
                                    <p class="text-muted small fw-semibold text-uppercase mb-2">
                                        <i class="bi bi-code-square me-1"></i>Détails de l'opération
                                    </p>
                                    <?= renderDiff($details) ?>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>

                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
function renderDiff(array $details): string
{
    $html = '';

    if (isset($details['before'], $details['after'])) {
        $before = (array) $details['before'];
        $after  = (array) $details['after'];
        $keys   = array_unique(array_merge(array_keys($before), array_keys($after)));

        $html .= '<table class="table table-sm table-bordered mb-0" style="font-size:0.8rem;">';
        $html .= '<thead class="table-light"><tr><th>Champ</th><th class="text-danger">Avant</th><th class="text-success">Après</th></tr></thead><tbody>';

        foreach ($keys as $key) {
            $bVal = $before[$key] ?? null;
            $aVal = $after[$key]  ?? null;
            if ($bVal === $aVal) continue;
            $html .= '<tr class="table-warning">';
            $html .= '<td class="fw-semibold">'  . htmlspecialchars((string) $key,            ENT_QUOTES) . '</td>';
            $html .= '<td class="text-danger">'  . htmlspecialchars((string) ($bVal ?? '—'),  ENT_QUOTES) . '</td>';
            $html .= '<td class="text-success">' . htmlspecialchars((string) ($aVal ?? '—'),  ENT_QUOTES) . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';
    } else {
        $html .= '<dl class="row mb-0" style="font-size:0.82rem;">';
        foreach ($details as $key => $val) {
            $displayVal = is_array($val) ? json_encode($val, JSON_UNESCAPED_UNICODE) : (string) $val;
            $html .= '<dt class="col-sm-3 text-muted">' . htmlspecialchars((string) $key,       ENT_QUOTES) . '</dt>';
            $html .= '<dd class="col-sm-9">'            . htmlspecialchars($displayVal,         ENT_QUOTES) . '</dd>';
        }
        $html .= '</dl>';
    }

    return $html;
}
?>

<style>
.btn-xs { font-size: 0.7rem; line-height: 1.4; }
</style>

<script>
window.YesPageInit = function () {
    document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(btn => {
        btn.addEventListener('click', function () {
            const icon = this.querySelector('i');
            if (icon) {
                icon.classList.toggle('bi-chevron-down');
                icon.classList.toggle('bi-chevron-up');
            }
        });
    });
};
</script>