<?php

/**
 * YES - Your Event Solution
 *
 * Layout : Sidebar de navigation.
 *
 * @file sidebar.php
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.0
 * @since 2026
 *
 * Variables attendues :
 * @var string      $page          Page courante.
 * @var array       $t             Traductions chargées.
 * @var string      $sidebarNom    Nom complet de l'utilisateur connecté.
 * @var string|null $sidebarAvatar Nom du fichier avatar (ou null).
 * @var bool        $isAdmin       L'utilisateur est-il admin ?
 */

declare(strict_types=1);
?>
<nav class="sidebar d-flex flex-column flex-shrink-0 text-white shadow" aria-label="Navigation principale">

    <header class="d-flex align-items-center sidebar-header p-4 mb-2 position-relative">
        <a href="?page=dashboard" class="text-white text-decoration-none d-flex align-items-center logo-link"
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
        <li class="nav-item mb-1" role="none">
            <a href="?page=dashboard"
               class="nav-link text-white d-flex align-items-center py-3 <?= $page === 'dashboard' ? 'active bg-primary' : 'opacity-75' ?>"
               role="menuitem"
               <?= $page === 'dashboard' ? 'aria-current="page"' : '' ?>>
                <i class="bi bi-grid-1x2-fill fs-5 mx-2" aria-hidden="true"></i>
                <span class="ms-2 sb-text"><?= htmlspecialchars($t['nav_dashboard'], ENT_QUOTES) ?></span>
            </a>
        </li>
        <li class="nav-item mb-1" role="none">
            <a href="?page=annuaire"
               class="nav-link text-white d-flex align-items-center py-3 <?= $page === 'annuaire' ? 'active bg-primary' : 'opacity-75' ?>"
               role="menuitem"
               <?= $page === 'annuaire' ? 'aria-current="page"' : '' ?>>
                <i class="bi bi-people-fill fs-5 mx-2" aria-hidden="true"></i>
                <span class="ms-2 sb-text"><?= htmlspecialchars($t['nav_directory'], ENT_QUOTES) ?></span>
            </a>
        </li>
        <li class="nav-item mb-1" role="none">
            <a href="?page=nouvel_event"
               class="nav-link text-white d-flex align-items-center py-3 <?= $page === 'nouvel_event' ? 'active bg-primary' : 'opacity-75' ?>"
               role="menuitem"
               <?= $page === 'nouvel_event' ? 'aria-current="page"' : '' ?>>
                <i class="bi bi-calendar-event-fill fs-5 mx-2" aria-hidden="true"></i>
                <span class="ms-2 sb-text"><?= htmlspecialchars($t['nav_new_event'], ENT_QUOTES) ?></span>
            </a>
        </li>
        <li class="nav-item mb-1" role="none">
            <a href="?page=staff"
               class="nav-link text-white d-flex align-items-center py-3 <?= $page === 'staff' ? 'active bg-primary' : 'opacity-75' ?>"
               role="menuitem"
               <?= $page === 'staff' ? 'aria-current="page"' : '' ?>>
                <i class="bi bi-people-fill fs-5 mx-2" aria-hidden="true"></i>
                <span class="ms-2 sb-text"><?= htmlspecialchars($t['nav_staff'], ENT_QUOTES) ?></span>
            </a>
        </li>

        <?php if ($isAdmin): ?>
        <li class="nav-item mb-1 border-top border-secondary mt-3 pt-3" role="none">
            <small class="text-white-50 text-uppercase px-3 sb-text d-block mb-1"
                   style="font-size: 0.7rem;">Administration</small>
            <a href="?page=utilisateurs"
               class="nav-link text-white d-flex align-items-center py-3 <?= $page === 'utilisateurs' ? 'active bg-primary' : 'opacity-75' ?>"
               role="menuitem"
               <?= $page === 'utilisateurs' ? 'aria-current="page"' : '' ?>>
                <i class="bi bi-gear-fill fs-5 mx-2" aria-hidden="true"></i>
                <span class="ms-2 sb-text"><?= htmlspecialchars($t['nav_settings'], ENT_QUOTES) ?></span>
            </a>
        </li>
        <?php endif; ?>
    </ul>

    <footer class="p-3 border-top border-secondary mx-2 mb-3">
        <a href="?page=profil" class="d-flex align-items-center user-block text-decoration-none text-white"
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