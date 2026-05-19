<?php

/**
 * YES - Your Event Solution
 *
 * Layout : Sidebar de navigation.
 *
 * @file sidebar.php
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 2.0
 * @since 2026
 */

declare(strict_types=1);

$isProjectPage = in_array($page, ['projets', 'projet_detail'], true);
?>

<!-- ── Bouton hamburger (mobile uniquement) ── -->
<button id="hamburgerBtn"
        class="hamburger-btn d-flex d-md-none align-items-center justify-content-center"
        aria-label="Ouvrir le menu"
        aria-expanded="false"
        aria-controls="mobileSidebar">
    <i class="bi bi-list fs-4" aria-hidden="true"></i>
</button>

<!-- ── Overlay (mobile) ── -->
<div id="sidebarOverlay" class="sidebar-overlay" aria-hidden="true"></div>

<!-- ── Sidebar ── -->
<nav class="sidebar d-flex flex-column flex-shrink-0 text-white shadow" id="mobileSidebar" aria-label="Navigation principale">

    <header class="d-flex align-items-center sidebar-header p-4 mb-2 position-relative">
        <a href="/dashboard" class="text-white text-decoration-none d-flex align-items-center logo-link"
           aria-label="Accueil – <?= htmlspecialchars($t['app_name'], ENT_QUOTES) ?>">
            <figure class="bg-white rounded-2 d-flex align-items-center justify-content-center flex-shrink-0 overflow-hidden shadow-sm logo-box mb-0">
                <img src="assets/img/YES-Your-Event-Solution.png" alt="Logo YES" width="35" height="35"
                     style="object-fit: contain;">
            </figure>
            <span class="fs-5 fw-bold ms-2 sb-text" style="letter-spacing: 1px;">YES</span>
        </a>
        <button id="sidebarToggle" class="btn btn-link text-white p-0 toggle-btn"
                aria-label="Réduire / Agrandir la sidebar">
            <i class="bi bi-chevron-left" id="toggleIcon" aria-hidden="true"></i>
        </button>
    </header>

    <ul class="nav nav-pills flex-column mb-auto px-2" role="menubar">

        <!-- Dashboard -->
        <li class="nav-item mb-1" role="none">
            <a href="/dashboard"
               class="nav-link text-white d-flex align-items-center py-3 <?= $page === 'dashboard' ? 'active bg-primary' : 'opacity-75' ?>"
               role="menuitem"
               <?= $page === 'dashboard' ? 'aria-current="page"' : '' ?>>
                <i class="bi bi-grid-1x2-fill fs-5 mx-2" aria-hidden="true"></i>
                <span class="ms-2 sb-text"><?= htmlspecialchars($t['nav_dashboard'], ENT_QUOTES) ?></span>
            </a>
        </li>

        <!-- Projets -->
        <li class="nav-item mb-1" role="none">
            <a href="/projets"
               class="nav-link text-white d-flex align-items-center py-3 <?= $isProjectPage ? 'active bg-primary' : 'opacity-75' ?>"
               role="menuitem"
               <?= $isProjectPage ? 'aria-current="page"' : '' ?>>
                <i class="bi bi-kanban-fill fs-5 mx-2" aria-hidden="true"></i>
                <span class="ms-2 sb-text"><?= htmlspecialchars($t['nav_projects'], ENT_QUOTES) ?></span>
            </a>
        </li>

        <!-- Opérationnel (Planning · Matériel · Facturation · Budget) -->
        <li class="nav-item mb-1" role="none">
            <a href="/operationnel"
               class="nav-link text-white d-flex align-items-center py-3 <?= $page === 'operationnel' ? 'active bg-primary' : 'opacity-75' ?>"
               role="menuitem"
               <?= $page === 'operationnel' ? 'aria-current="page"' : '' ?>>
                <i class="bi bi-clipboard2-data-fill fs-5 mx-2" aria-hidden="true"></i>
                <span class="ms-2 sb-text">Opérationnel</span>
            </a>
        </li>

        <!-- Annuaire -->
        <li class="nav-item mb-1" role="none">
            <a href="/annuaire"
               class="nav-link text-white d-flex align-items-center py-3 <?= $page === 'annuaire' ? 'active bg-primary' : 'opacity-75' ?>"
               role="menuitem"
               <?= $page === 'annuaire' ? 'aria-current="page"' : '' ?>>
                <i class="bi bi-people-fill fs-5 mx-2" aria-hidden="true"></i>
                <span class="ms-2 sb-text"><?= htmlspecialchars($t['nav_directory'], ENT_QUOTES) ?></span>
            </a>
        </li>

        <!-- Nouvel Événement -->
        <li class="nav-item mb-1" role="none">
            <a href="/nouvel_event"
               class="nav-link text-white d-flex align-items-center py-3 <?= $page === 'nouvel_event' ? 'active bg-primary' : 'opacity-75' ?>"
               role="menuitem"
               <?= $page === 'nouvel_event' ? 'aria-current="page"' : '' ?>>
                <i class="bi bi-calendar-event-fill fs-5 mx-2" aria-hidden="true"></i>
                <span class="ms-2 sb-text"><?= htmlspecialchars($t['nav_new_event'], ENT_QUOTES) ?></span>
            </a>
        </li>

        <!-- Staff -->
        <li class="nav-item mb-1" role="none">
            <a href="/staff"
               class="nav-link text-white d-flex align-items-center py-3 <?= $page === 'staff' ? 'active bg-primary' : 'opacity-75' ?>"
               role="menuitem"
               <?= $page === 'staff' ? 'aria-current="page"' : '' ?>>
                <i class="bi bi-person-badge-fill fs-5 mx-2" aria-hidden="true"></i>
                <span class="ms-2 sb-text"><?= htmlspecialchars($t['nav_staff'], ENT_QUOTES) ?></span>
            </a>
        </li>

        <?php if ($isAdmin): ?>
        <li class="nav-item mb-1 border-top border-secondary mt-3 pt-3" role="none">
            <small class="text-white-50 text-uppercase px-3 sb-text d-block mb-1"
                   style="font-size: 0.7rem;">Administration</small>
            <a href="/utilisateurs"
               class="nav-link text-white d-flex align-items-center py-3 <?= $page === 'utilisateurs' ? 'active bg-primary' : 'opacity-75' ?>"
               role="menuitem"
               <?= $page === 'utilisateurs' ? 'aria-current="page"' : '' ?>>
                <i class="bi bi-gear-fill fs-5 mx-2" aria-hidden="true"></i>
                <span class="ms-2 sb-text"><?= htmlspecialchars($t['nav_settings'], ENT_QUOTES) ?></span>
            </a>
        </li>
        <?php endif; ?>
    </ul>

    <!-- ── Section mobile : actions du header ── -->
    <div class="d-flex d-md-none flex-column px-3 py-2 border-top border-secondary mx-2 mobile-header-actions">
        <div class="dropdown mb-2">
            <button class="btn btn-primary btn-sm w-100 fw-bold dropdown-toggle"
                    type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <?= htmlspecialchars($t['qc_btn_add'], ENT_QUOTES) ?>
            </button>
            <ul class="dropdown-menu shadow border-0 mt-2 w-100" role="menu">
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
        <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
            <nav class="btn-group border border-secondary rounded-pill overflow-hidden" role="group" aria-label="Langue">
                <a href="/change_lang?lang=fr&return=<?= htmlspecialchars($page, ENT_QUOTES) ?>"
                   class="btn btn-xs py-1 px-3 <?= $lang === 'fr' ? 'bg-body-secondary fw-bold text-dark' : 'text-white opacity-75' ?>"
                   hreflang="fr" lang="fr">FR</a>
                <a href="/change_lang?lang=en&return=<?= htmlspecialchars($page, ENT_QUOTES) ?>"
                   class="btn btn-xs py-1 px-3 <?= $lang === 'en' ? 'bg-body-secondary fw-bold text-dark' : 'text-white opacity-75' ?>"
                   hreflang="en" lang="en">EN</a>
            </nav>
            <button id="darkModeToggleMobile" class="btn btn-link text-white p-0 fs-5 shadow-none"
                    aria-label="Basculer entre mode clair et sombre">
                <?php if ($theme === 'dark'): ?>
                    <i class="bi bi-moon-stars-fill text-warning" aria-hidden="true"></i>
                <?php else: ?>
                    <i class="bi bi-sun-fill text-warning" aria-hidden="true"></i>
                <?php endif; ?>
            </button>
        </div>
    </div>

    <footer class="p-3 border-top border-secondary mx-2 mb-3">
        <a href="/profil" class="d-flex align-items-center user-block text-decoration-none text-white"
           aria-label="<?= htmlspecialchars($t['nav_profile'], ENT_QUOTES) ?>">
            <figure class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0 shadow-sm overflow-hidden mb-0 avatar-circle">
                <?php if ($sidebarAvatar): ?>
                    <img src="uploads/avatars/<?= htmlspecialchars($sidebarAvatar, ENT_QUOTES) ?>"
                         alt="Avatar de <?= htmlspecialchars($sidebarNom, ENT_QUOTES) ?>"
                         style="width: 100%; height: 100%; object-fit: cover;">
                <?php else: ?>
                    <?= strtoupper(substr($sidebarNom, 0, 1)) ?>
                <?php endif; ?>
            </figure>
            <section class="ms-3 sb-text">
                <p class="fw-bold text-truncate mb-0" style="font-size: 0.85rem;">
                    <?= htmlspecialchars($sidebarNom, ENT_QUOTES) ?>
                </p>
                <p class="text-white-50 small text-capitalize fw-semibold mb-0">
                    <?= htmlspecialchars((string) $_SESSION['user_role'], ENT_QUOTES) ?>
                </p>
            </section>
        </a>
    </footer>

</nav>