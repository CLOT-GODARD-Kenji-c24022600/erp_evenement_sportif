<nav class="sidebar d-flex flex-column flex-shrink-0 text-white shadow" style="background-color: #1e293b; transition: all 0.3s ease;">
    
    <div class="d-flex align-items-center sidebar-header p-4 mb-2 position-relative" style="min-height: 80px;">
        <a href="?page=dashboard" class="text-white text-decoration-none d-flex align-items-center logo-link">
            <div class="bg-primary rounded-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 35px; height: 35px;">
                <span class="fw-bold">SE</span>
            </div>
            <span class="fs-5 fw-bold ms-2 sb-text">SportEvent</span>
        </a>

        <button id="sidebarToggle" class="btn btn-link text-white p-0 toggle-btn">
            <i class="bi bi-chevron-left shadow-none" id="toggleIcon"></i>
        </button>
    </div>

    <ul class="nav nav-pills flex-column mb-auto px-2">
        <li class="nav-item mb-1">
            <a href="?page=dashboard" class="nav-link text-white d-flex align-items-center py-3 <?= ($page==='dashboard')?'active bg-primary':'opacity-75' ?>">
                <i class="bi bi-grid-1x2-fill fs-5 mx-2"></i>
                <span class="ms-2 sb-text">Dashboard</span>
            </a>
        </li>
        <li class="mb-1">
            <a href="?page=annuaire" class="nav-link text-white d-flex align-items-center py-3 <?= ($page==='annuaire')?'active bg-primary':'opacity-75' ?>">
                <i class="bi bi-people-fill fs-5 mx-2"></i>
                <span class="ms-2 sb-text">Staff</span>
            </a>
        </li>
        <li class="mb-1">
            <a href="?page=nouvel_event" class="nav-link text-white d-flex align-items-center py-3 <?= ($page==='nouvel_event')?'active bg-primary':'opacity-75' ?>">
                <i class="bi bi-calendar-event-fill fs-5 mx-2"></i>
                <span class="ms-2 sb-text">Events</span>
            </a>
        </li>
        <?php if ($_SESSION['user_role'] === 'admin'): ?>
        <li class="mb-1">
            <a href="?page=utilisateurs" class="nav-link text-white d-flex align-items-center py-3 <?= ($page==='utilisateurs')?'active bg-primary':'opacity-75' ?>">
                <i class="bi bi-gear-fill fs-5 mx-2"></i>
                <span class="ms-2 sb-text">Settings</span>
            </a>
        </li>
        <?php endif; ?>
    </ul>

    <div class="p-3 border-top border-secondary mx-2 mb-3">
        <div class="d-flex align-items-center user-block">
            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0" style="width: 40px; height: 40px;">
                <?= strtoupper(substr($_SESSION['user_nom'], 0, 1)) ?>
            </div>
            <div class="ms-3 sb-text">
                <div class="fw-bold text-truncate" style="font-size: 0.85rem;"><?= htmlspecialchars($_SESSION['user_nom']) ?></div>
                <div class="text-muted small text-capitalize"><?= $_SESSION['user_role'] ?></div>
            </div>
        </div>
    </div>
</nav>

<style>
    /* --- STYLE DE BASE --- */
    .toggle-btn {
        position: absolute;
        right: 15px;
        transition: var(--transition);
    }

    /* --- ÉTAT REPLIÉ (COLLAPSED) --- */

    /* 1. Cacher tout le logo et le texte SportEvent */
    body.collapsed .logo-link { 
        display: none !important; 
    }

    /* 2. Cacher tous les textes du menu et du profil */
    body.collapsed .sb-text { 
        display: none !important; 
    }

    /* 3. Centrer la flèche au milieu de la barre de 80px */
    body.collapsed .sidebar-header {
        justify-content: center !important;
        padding: 0 !important;
    }

    body.collapsed .toggle-btn {
        position: static; /* Sort de l'absolu pour se centrer naturellement */
        margin: 0 auto;
        font-size: 1.5rem;
    }

    /* 4. Retourner la flèche */
    body.collapsed #toggleIcon { 
        transform: rotate(180deg); 
    }

    /* 5. Centrer les icônes du menu et du profil */
    body.collapsed .sidebar .nav-link,
    body.collapsed .user-block { 
        justify-content: center !important; 
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    body.collapsed .sidebar .nav-link i {
        margin: 0 !important;
    }
</style>