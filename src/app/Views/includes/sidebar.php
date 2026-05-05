<?php
// On récupère les infos fraîches de l'utilisateur directement depuis la BDD !
$stmt_sb = $db->prepare("SELECT prenom, nom, avatar FROM utilisateurs WHERE id = ?");
$stmt_sb->execute([$_SESSION['user_id']]);
$user_sb = $stmt_sb->fetch();

// On construit le nom complet (Prénom + Nom)
$sidebar_nom = trim(($user_sb['prenom'] ?? '') . ' ' . $user_sb['nom']);
if (empty($sidebar_nom)) {
    $sidebar_nom = $_SESSION['user_nom']; // Sécurité si jamais c'est vide
}
$sidebar_avatar = $user_sb['avatar'] ?? null;
?>

<nav class="sidebar d-flex flex-column flex-shrink-0 text-white shadow" style="background-color: #1e293b; transition: all 0.3s ease;">
    
    <div class="d-flex align-items-center sidebar-header p-4 mb-2 position-relative" style="min-height: 80px;">
        <a href="?page=dashboard" class="text-white text-decoration-none d-flex align-items-center logo-link">
            
            <div class="bg-white rounded-2 d-flex align-items-center justify-content-center flex-shrink-0 overflow-hidden shadow-sm" style="width: 35px; height: 35px;">
                <img src="assets/img/YES-Your-Event-Solution.png" alt="YES Logo" style="width: 100%; height: 100%; object-fit: contain;">
            </div>
            
            <span class="fs-5 fw-bold ms-2 sb-text" style="letter-spacing: 1px;">YES</span>
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
                <span class="ms-2 sb-text">Ressource</span>
            </a>
        </li>
        <li class="mb-1">
            <a href="?page=nouvel_event" class="nav-link text-white d-flex align-items-center py-3 <?= ($page==='nouvel_event')?'active bg-primary':'opacity-75' ?>">
                <i class="bi bi-calendar-event-fill fs-5 mx-2"></i>
                <span class="ms-2 sb-text">Events</span>
            </a>
        </li>
        <li class="mb-1">
            <a href="?page=staff" class="nav-link text-white d-flex align-items-center py-3 <?= ($page==='staff')?'active bg-primary':'opacity-75' ?>">
                <i class="bi bi-people-fill fs-5 mx-2"></i>
                <span class="ms-2 sb-text">Staff</span>
            </a>
        </li>
        
        <?php if ($_SESSION['user_role'] === 'admin'): ?>
        <li class="mb-1 border-top border-secondary mt-3 pt-3">
            <small class="text-white-50 text-uppercase px-3 sb-text" style="font-size: 0.7rem;">Administration</small>
            <a href="?page=utilisateurs" class="nav-link text-white d-flex align-items-center py-3 <?= ($page==='utilisateurs')?'active bg-primary':'opacity-75' ?>">
                <i class="bi bi-gear-fill fs-5 mx-2"></i>
                <span class="ms-2 sb-text">Settings</span>
            </a>
        </li>
        <?php endif; ?>
    </ul>

    <div class="p-3 border-top border-secondary mx-2 mb-3">
        <a href="?page=profil" class="d-flex align-items-center user-block text-decoration-none text-white transition-hover">
            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0 shadow-sm overflow-hidden" style="width: 40px; height: 40px;">
                
                <?php if ($sidebar_avatar): ?>
                    <img src="uploads/avatars/<?= htmlspecialchars($sidebar_avatar) ?>" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover;">
                <?php else: ?>
                    <?= strtoupper(substr($sidebar_nom, 0, 1)) ?>
                <?php endif; ?>
                
            </div>
            <div class="ms-3 sb-text">
                <div class="fw-bold text-truncate" style="font-size: 0.85rem;"><?= htmlspecialchars($sidebar_nom) ?></div>
                
                <div class="text-white-50 small text-capitalize fw-semibold"><?= $_SESSION['user_role'] ?></div>
            </div>
        </a>
    </div>
</nav>

<style>
    .toggle-btn {
        position: absolute;
        right: 15px;
        transition: all 0.3s ease;
    }

    .transition-hover:hover {
        opacity: 0.85;
        background-color: rgba(255,255,255,0.05);
        border-radius: 8px;
    }

    body.collapsed .logo-link,
    body.collapsed .sb-text { 
        display: none !important; 
    }

    body.collapsed .sidebar-header {
        justify-content: center !important;
        padding: 0 !important;
    }

    body.collapsed .toggle-btn {
        position: static;
        margin: 0 auto;
        font-size: 1.5rem;
    }

    body.collapsed #toggleIcon { 
        transform: rotate(180deg); 
    }

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