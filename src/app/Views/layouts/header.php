<?php

/**
 * YES - Your Event Solution
 *
 * Layout : En-tête principal de l'application (topbar uniquement).
 * Le <head> et le <body> sont gérés par Renderer::renderApp().
 * Les modales Quick-Create sont dans footer.php (après </main>).
 *
 * @file header.php
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.4
 * @since 2026
 */

declare(strict_types=1);
?>
<main class="main-content">

    <header class="top-bar d-flex align-items-center px-3 px-md-4">
        <div class="d-flex justify-content-between w-100 align-items-center gap-2">

            <!-- Breadcrumb (desktop) / Titre page (mobile) -->
            <nav aria-label="breadcrumb" class="flex-shrink-0">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item text-muted small d-none d-md-block">Projects</li>
                    <li class="breadcrumb-item active fw-bold small text-dark-emphasis">
                        <?= ucfirst(htmlspecialchars($page, ENT_QUOTES)) ?>
                    </li>
                </ol>
            </nav>

            <!-- Barre de recherche -->
            <search aria-label="Recherche globale" class="position-relative flex-grow-1 flex-md-grow-0">
                <div class="input-group">
                    <span class="input-group-text bg-body-secondary border-0 text-muted search-btn">
                        <i class="bi bi-search small" aria-hidden="true"></i>
                    </span>
                    <input type="text"
                           id="globalSearchInput"
                           class="form-control form-control-sm border-0 bg-body-secondary px-3 shadow-none search-input"
                           placeholder="Rechercher..."
                           autocomplete="off"
                           aria-label="Terme de recherche"
                           aria-expanded="false"
                           aria-controls="searchDropdown">
                </div>
                <div id="searchDropdown"
                     class="d-none position-absolute bg-body border shadow rounded-3 mt-1 overflow-hidden"
                     role="listbox"
                     style="top:100%; left:0; min-width:280px; z-index:1080; max-height:400px; overflow-y:auto;">
                </div>
            </search>

            <!-- Actions desktop -->
            <nav class="d-none d-md-flex align-items-center gap-3" aria-label="Actions globales">

                <div class="dropdown">
                    <button class="btn btn-primary btn-sm px-3 fw-bold shadow-sm dropdown-toggle"
                            type="button"
                            data-bs-toggle="dropdown"
                            aria-expanded="false">
                        <?= htmlspecialchars($t['qc_btn_add'], ENT_QUOTES) ?>
                    </button>
                    <ul class="dropdown-menu shadow border-0 mt-2" role="menu">
                        <li role="none">
                            <a class="dropdown-item py-2" href="#" role="menuitem"
                               data-bs-toggle="modal" data-bs-target="#modalEvent">
                                <i class="bi bi-calendar-event me-2 text-success" aria-hidden="true"></i>
                                <?= htmlspecialchars($t['qc_event_title'], ENT_QUOTES) ?>
                            </a>
                        </li>
                        <li role="none">
                            <a class="dropdown-item py-2" href="#" role="menuitem"
                               data-bs-toggle="modal" data-bs-target="#modalProjet">
                                <i class="bi bi-folder me-2 text-warning" aria-hidden="true"></i>
                                <?= htmlspecialchars($t['qc_projet_title'], ENT_QUOTES) ?>
                            </a>
                        </li>
                        <?php if ($isAdmin): ?>
                            <li role="none"><hr class="dropdown-divider"></li>
                            <li role="none">
                                <a class="dropdown-item py-2" href="#" role="menuitem"
                                   data-bs-toggle="modal" data-bs-target="#modalUser">
                                    <i class="bi bi-person-plus me-2 text-primary" aria-hidden="true"></i>
                                    <?= htmlspecialchars($t['qc_user_title'], ENT_QUOTES) ?>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>

                <nav class="btn-group border rounded-pill overflow-hidden" role="group" aria-label="Sélection de la langue">
                    <a href="/change_lang?lang=fr&return=<?= htmlspecialchars($page, ENT_QUOTES) ?>"
                       class="btn btn-xs py-0 px-2 <?= $lang === 'fr' ? 'bg-body-secondary fw-bold text-dark' : 'text-muted' ?>"
                       hreflang="fr" lang="fr">FR</a>
                    <a href="/change_lang?lang=en&return=<?= htmlspecialchars($page, ENT_QUOTES) ?>"
                       class="btn btn-xs py-0 px-2 <?= $lang === 'en' ? 'bg-body-secondary fw-bold text-dark' : 'text-muted' ?>"
                       hreflang="en" lang="en">EN</a>
                </nav>

                <button id="darkModeToggle" class="btn btn-link text-body p-0 fs-5 shadow-none"
                        aria-label="Basculer entre mode clair et sombre">
                    <?php if ($theme === 'dark'): ?>
                        <i class="bi bi-moon-stars-fill text-warning" aria-hidden="true"></i>
                    <?php else: ?>
                        <i class="bi bi-sun-fill text-warning" aria-hidden="true"></i>
                    <?php endif; ?>
                </button>

            </nav>

            <!-- Notifications -->
            <div class="dropdown flex-shrink-0">
<?php
$notifCount = count($notifications ?? []);
$notifIcons = [
    'todo_retard'       => ['icon' => 'bi-clock-history',        'color' => 'danger'],
    'budget_depasse'    => ['icon' => 'bi-exclamation-triangle', 'color' => 'warning'],
    'event_proche'      => ['icon' => 'bi-calendar-event',       'color' => 'primary'],
    'facture_manquante' => ['icon' => 'bi-receipt',              'color' => 'info'],
    'tache_assignee'    => ['icon' => 'bi-person-check',         'color' => 'success'],
    'projet_statut'     => ['icon' => 'bi-kanban',               'color' => 'secondary'],
    'info'              => ['icon' => 'bi-info-circle',          'color' => 'secondary'],
];
function notif_time_ago(string $dt): string {
    $d = time() - strtotime($dt);
    if ($d < 60)     return "À l'instant";
    if ($d < 3600)   return floor($d/60).' min';
    if ($d < 86400)  return floor($d/3600).'h';
    if ($d < 604800) return floor($d/86400).'j';
    return date('d/m/Y', strtotime($dt));
}
?>
                <button class="btn btn-link text-body p-0 fs-5 position-relative shadow-none"
                        type="button"
                        data-bs-toggle="dropdown"
                        data-bs-auto-close="outside"
                        aria-expanded="false"
                        aria-label="Notifications">
                    <i class="bi bi-bell" id="notif-bell-icon" aria-hidden="true"></i>
                    <span id="notif-badge"
                          class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                          style="font-size:.6rem;min-width:18px;<?= $notifCount === 0 ? 'display:none;' : '' ?>">
                        <?= $notifCount > 99 ? '99+' : $notifCount ?>
                    </span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end notif-dropdown shadow p-0" role="menu"
                    style="min-width:340px;max-height:460px;overflow-y:auto;">
                    <li class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom sticky-top bg-body" role="none">
                        <h6 class="mb-0 fw-bold text-primary">
                            <i class="bi bi-bell me-1"></i><?= htmlspecialchars($t['notif_title'], ENT_QUOTES) ?>
                            <?php if ($notifCount > 0): ?>
                            <span id="notif-count-header" class="badge bg-danger ms-1"><?= $notifCount ?></span>
                            <?php endif; ?>
                        </h6>
                        <?php if ($notifCount > 0): ?>
                        <button class="btn btn-link btn-sm text-muted p-0 text-decoration-none" id="notif-mark-all-btn">
                            <i class="bi bi-check2-all me-1"></i><small>Tout lire</small>
                        </button>
                        <?php endif; ?>
                    </li>
                    <?php if ($notifCount === 0): ?>
                    <li role="none">
                        <span class="dropdown-item text-muted small py-4 text-center d-block" role="menuitem">
                            <i class="bi bi-bell-slash fs-3 d-block mb-2 opacity-50"></i>
                            <?= htmlspecialchars($t['notif_empty'], ENT_QUOTES) ?>
                        </span>
                    </li>
                    <?php else: ?>
                    <?php foreach ($notifications as $notif):
                        $ni = $notifIcons[$notif['type'] ?? 'info'] ?? $notifIcons['info'];
                    ?>
                    <li class="notif-item border-bottom"
                        data-id="<?= (int)$notif['id'] ?>"
                        role="none"
                        style="background:<?= $notif['lu'] ? 'transparent' : 'var(--bs-primary-bg-subtle)' ?>;">
                        <div class="d-flex gap-2 px-3 py-2 align-items-start"
                             style="cursor:pointer;"
                             onclick="notifClick(<?= (int)$notif['id'] ?>, '<?= htmlspecialchars($notif['lien'] ?? '', ENT_QUOTES) ?>')">
                            <span class="badge bg-<?= $ni['color'] ?>-subtle text-<?= $ni['color'] ?> rounded-circle p-2 flex-shrink-0 mt-1">
                                <i class="bi <?= $ni['icon'] ?>"></i>
                            </span>
                            <div class="flex-grow-1 overflow-hidden">
                                <p class="fw-semibold mb-0 small text-truncate">
                                    <?= htmlspecialchars((string)$notif['titre'], ENT_QUOTES) ?>
                                </p>
                                <?php if (!empty($notif['message'])): ?>
                                <p class="text-body-secondary mb-0" style="font-size:.78rem;">
                                    <?= htmlspecialchars((string)$notif['message'], ENT_QUOTES) ?>
                                </p>
                                <?php endif; ?>
                                <p class="text-muted mb-0" style="font-size:.72rem;">
                                    <i class="bi bi-clock me-1"></i><?= notif_time_ago((string)$notif['created_at']) ?>
                                </p>
                            </div>
                            <button class="btn btn-link p-0 text-muted flex-shrink-0"
                                    onclick="notifDelete(event, <?= (int)$notif['id'] ?>)"
                                    title="Supprimer">
                                <i class="bi bi-x-lg" style="font-size:.75rem;"></i>
                            </button>
                        </div>
                    </li>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>

        </div>
    </header>

    <?php if ($qcMsg !== ''): ?>
        <aside class="alert alert-<?= $qcType === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show m-3 position-absolute top-0 end-0 z-3 shadow qc-alert"
               role="alert">
            <i class="bi <?= $qcType === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' ?> me-2"
               aria-hidden="true"></i>
            <?= htmlspecialchars($qcMsg, ENT_QUOTES) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"
                    aria-label="<?= htmlspecialchars($t['btn_close'], ENT_QUOTES) ?>"></button>
        </aside>
    <?php endif; ?>