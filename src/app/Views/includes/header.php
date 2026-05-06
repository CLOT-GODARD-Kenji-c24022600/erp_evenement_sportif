<?php
use Core\Database;

if (session_status() === PHP_SESSION_NONE) session_start();
$lang = $_SESSION['lang'] ?? 'fr';
$t = require __DIR__ . '/../../Language/' . $lang . '.php';
$theme = $_COOKIE['theme'] ?? 'light';

$db = Database::getConnection();

// --- 1. LOGIQUE QUICK CREATE ---
$qc_msg = '';
$qc_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quick_create'])) {
    $action = $_POST['quick_create'];
    try {
        if ($action === 'event') {
            $stmt = $db->prepare("INSERT INTO evenements (nom, description, date_debut, date_fin, lieu) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$_POST['nom'], $_POST['description'], $_POST['date_debut'], $_POST['date_fin'], $_POST['lieu']]);
            $qc_msg = "Événement créé avec succès !";
            $qc_type = "success";
        } elseif ($action === 'projet') {
            $stmt = $db->prepare("INSERT INTO projets (nom, description, date_creation) VALUES (?, ?, NOW())");
            $stmt->execute([$_POST['nom'], $_POST['description']]);
            $qc_msg = "Projet créé avec succès !";
            $qc_type = "success";
        } elseif ($action === 'user' && $_SESSION['user_role'] === 'admin') {
            $mdp_hash = password_hash('Bienvenue123!', PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO utilisateurs (prenom, nom, email, mot_de_passe, poste, role, statut) VALUES (?, ?, ?, ?, ?, ?, 'approuve')");
            $stmt->execute([$_POST['prenom'], $_POST['nom'], $_POST['email'], $mdp_hash, $_POST['poste'], $_POST['role']]);
            $qc_msg = "Membre ajouté ! Mdp provisoire : Bienvenue123!";
            $qc_type = "success";
        }
    } catch (PDOException $e) {
        $qc_msg = "Erreur lors de la création : " . $e->getMessage();
        $qc_type = "danger";
    }
}

// --- 2. LOGIQUE POUR LES NOTIFICATIONS ---
$notifications = [];
if (isset($_SESSION['user_id'])) {
    try {
        $stmt_notifs = $db->query("SELECT id, nom, date_debut FROM evenements WHERE date_debut >= CURRENT_DATE ORDER BY date_debut ASC LIMIT 3");
        $notifications = $stmt_notifs->fetchAll();
    } catch (PDOException $e) {}
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" data-bs-theme="<?= $theme ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>Your Event Solution</title>
    
    <link rel="icon" type="image/png" href="assets/img/YES-Your-Event-Solution.png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root { --sb-width: 260px; --transition: all 0.3s ease; }
        body.collapsed { --sb-width: 80px; }
        body { font-family: 'Inter', sans-serif; background-color: var(--bs-tertiary-bg); transition: var(--transition); margin: 0; overflow-x: hidden; }
        [data-bs-theme="dark"] { --bs-body-bg: #0f172a; --bs-tertiary-bg: #1e293b; --bs-body-color: #f1f5f9; --bs-border-color: #334155; }
        .sidebar { width: var(--sb-width); height: 100vh; position: fixed; top: 0; left: 0; z-index: 1040; transition: var(--transition); background-color: #1e293b; overflow: hidden; }
        .main-content { margin-left: var(--sb-width); transition: var(--transition); min-height: 100vh; display: flex; flex-direction: column; width: calc(100% - var(--sb-width)); }
        .top-bar { height: 70px; background: var(--bs-body-bg); border-bottom: 1px solid var(--bs-border-color); position: sticky; top: 0; z-index: 1020; }

        /* Sidebar Repliée */
        body.collapsed .sb-text, body.collapsed .sidebar span:not(.fw-bold):not(.fs-5), body.collapsed .sidebar .text-muted, body.collapsed .sidebar .fw-bold.text-truncate, body.collapsed .sidebar .fs-5.fw-bold { display: none !important; }
        body.collapsed .sidebar .p-4.mb-2 { padding-left: 0 !important; padding-right: 0 !important; display: flex !important; justify-content: center !important; }
        body.collapsed .sidebar .p-4.mb-2 a { justify-content: center !important; width: 100% !important; margin: 0 !important; }
        body.collapsed .sidebar .p-4.mb-2 a div.logo-box { margin-right: 0 !important; flex-shrink: 0 !important; }
        body.collapsed .sidebar .nav-link { justify-content: center !important; padding: 1rem 0 !important; }
        body.collapsed .sidebar .nav-link i, body.collapsed .sidebar .nav-link span:first-child { margin: 0 !important; }
        body.collapsed .sidebar .p-3.border-top { padding-left: 0 !important; padding-right: 0 !important; display: flex !important; justify-content: center !important; }
        body.collapsed .sidebar .p-3.border-top .ms-3 { display: none !important; }
        body.collapsed .sidebar .p-3.border-top .d-flex { justify-content: center !important; width: 100%; }
        #sidebarToggle i { transition: transform 0.3s; }
        body.collapsed #sidebarToggle i { transform: rotate(180deg); }
        .notif-dropdown { width: 300px; max-height: 400px; overflow-y: auto; }
    </style>
</head>
<body class="<?= (isset($_COOKIE['sidebar']) && $_COOKIE['sidebar'] === 'collapsed') ? 'collapsed' : '' ?>">

<?php if (isset($_SESSION['user_id']) && !in_array($page, $pages_publiques)): ?>
    <div class="main-content">
        <header class="top-bar d-flex align-items-center px-4 relative">
            <div class="d-flex justify-content-between w-100 align-items-center">
                
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item text-muted small">Projects</li>
                        <li class="breadcrumb-item active fw-bold small text-dark-emphasis"><?= ucfirst($page) ?></li>
                    </ol>
                </nav>

                <div class="d-none d-md-block">
                    <form action="index.php" method="GET" class="mb-0">
                        <input type="hidden" name="page" value="recherche">
                        <div class="input-group">
                            <button type="submit" class="input-group-text bg-body-secondary border-0 text-muted" style="border-radius: 8px 0 0 8px;"><i class="bi bi-search small"></i></button>
                            <input type="text" name="q" class="form-control form-control-sm border-0 bg-body-secondary px-3 shadow-none" style="width: 300px; border-radius: 0 8px 8px 0;" placeholder="Rechercher (Staff, Projets...)" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" required>
                        </div>
                    </form>
                </div>

                <div class="d-flex align-items-center gap-3">
                    
                    <div class="dropdown">
                        <button class="btn btn-primary btn-sm px-3 fw-bold shadow-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            + Ajouter
                        </button>
                        <ul class="dropdown-menu shadow border-0 mt-2">
                            <li><a class="dropdown-item py-2" href="#" data-bs-toggle="modal" data-bs-target="#modalEvent"><i class="bi bi-calendar-event me-2 text-success"></i>Événement</a></li>
                            <li><a class="dropdown-item py-2" href="#" data-bs-toggle="modal" data-bs-target="#modalProjet"><i class="bi bi-folder me-2 text-warning"></i>Projet</a></li>
                            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item py-2" href="#" data-bs-toggle="modal" data-bs-target="#modalUser"><i class="bi bi-person-plus me-2 text-primary"></i>Membre du Staff</a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    
                    <div class="btn-group border rounded-pill overflow-hidden">
                        <a href="?page=change_lang&lang=fr&return=<?= $page ?>" class="btn btn-xs py-0 px-2 <?= $lang==='fr'?'bg-body-secondary fw-bold text-dark':'text-muted' ?>">FR</a>
                        <a href="?page=change_lang&lang=en&return=<?= $page ?>" class="btn btn-xs py-0 px-2 <?= $lang==='en'?'bg-body-secondary fw-bold text-dark':'text-muted' ?>">EN</a>
                    </div>
                    
                    <button id="darkModeToggle" class="btn btn-link text-body p-0 fs-5 shadow-none" title="Changer de thème">
                        <?php if ($theme === 'dark'): ?>
                            <i class="bi bi-moon-stars-fill text-warning"></i>
                        <?php else: ?>
                            <i class="bi bi-sun-fill text-warning"></i>
                        <?php endif; ?>
                    </button>
                    
                    <div class="dropdown">
                        <button class="btn btn-link text-body p-0 fs-5 position-relative shadow-none" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-bell"></i>
                            <?php if (count($notifications) > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle" style="width: 10px; height: 10px;"></span>
                            <?php endif; ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end notif-dropdown shadow">
                            <li><h6 class="dropdown-header text-primary fw-bold">Prochains Événements</h6></li>
                            <?php if (count($notifications) > 0): ?>
                                <?php foreach ($notifications as $notif): ?>
                                    <li>
                                        <a class="dropdown-item d-flex flex-column py-2" href="#">
                                            <span class="fw-bold fs-6 text-truncate"><?= htmlspecialchars($notif['nom']) ?></span>
                                            <small class="text-muted"><i class="bi bi-calendar-event me-1"></i><?= date('d/m/Y', strtotime($notif['date_debut'])) ?></small>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li><span class="dropdown-item text-muted small py-3 text-center">Aucun événement à venir.</span></li>
                            <?php endif; ?>
                        </ul>
                    </div>

                </div>
            </div>
        </header>

        <?php if ($qc_msg): ?>
            <div class="alert alert-<?= $qc_type ?> alert-dismissible fade show m-3 position-absolute top-0 end-0 z-3 shadow" role="alert" style="margin-top: 80px !important;">
                <i class="bi <?= $qc_type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' ?> me-2"></i>
                <?= $qc_msg ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="modal fade" id="modalEvent" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="" method="POST">
                        <input type="hidden" name="quick_create" value="event">
                        <div class="modal-header border-0 pb-0"><h5 class="modal-title fw-bold">Créer un événement</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                        <div class="modal-body">
                            <div class="mb-3"><label class="form-label small fw-bold">Nom de l'événement</label><input type="text" name="nom" class="form-control" required></div>
                            <div class="row mb-3">
                                <div class="col"><label class="form-label small fw-bold">Date de début</label><input type="date" name="date_debut" class="form-control" required></div>
                                <div class="col"><label class="form-label small fw-bold">Date de fin</label><input type="date" name="date_fin" class="form-control" required></div>
                            </div>
                            <div class="mb-3"><label class="form-label small fw-bold">Lieu</label><input type="text" name="lieu" class="form-control" required></div>
                            <div class="mb-3"><label class="form-label small fw-bold">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
                        </div>
                        <div class="modal-footer border-0 pt-0"><button type="submit" class="btn btn-success w-100 fw-bold">Créer l'événement</button></div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalProjet" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="" method="POST">
                        <input type="hidden" name="quick_create" value="projet">
                        <div class="modal-header border-0 pb-0"><h5 class="modal-title fw-bold">Nouveau projet</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                        <div class="modal-body">
                            <div class="mb-3"><label class="form-label small fw-bold">Nom du projet</label><input type="text" name="nom" class="form-control" required></div>
                            <div class="mb-3"><label class="form-label small fw-bold">Description courte</label><textarea name="description" class="form-control" rows="3"></textarea></div>
                        </div>
                        <div class="modal-footer border-0 pt-0"><button type="submit" class="btn btn-warning text-dark w-100 fw-bold">Créer le projet</button></div>
                    </form>
                </div>
            </div>
        </div>

        <?php if ($_SESSION['user_role'] === 'admin'): ?>
        <div class="modal fade" id="modalUser" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="" method="POST">
                        <input type="hidden" name="quick_create" value="user">
                        <div class="modal-header border-0 pb-0"><h5 class="modal-title fw-bold">Ajouter un membre</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                        <div class="modal-body">
                            <div class="row mb-3">
                                <div class="col"><label class="form-label small fw-bold">Prénom</label><input type="text" name="prenom" class="form-control" required></div>
                                <div class="col"><label class="form-label small fw-bold">Nom</label><input type="text" name="nom" class="form-control" required></div>
                            </div>
                            <div class="mb-3"><label class="form-label small fw-bold">Email</label><input type="email" name="email" class="form-control" required></div>
                            <div class="row mb-3">
                                <div class="col"><label class="form-label small fw-bold">Poste</label><input type="text" name="poste" class="form-control" placeholder="Ex: Coach"></div>
                                <div class="col"><label class="form-label small fw-bold">Rôle</label>
                                    <select name="role" class="form-select">
                                        <option value="staff">Staff</option>
                                        <option value="admin">Administrateur</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0"><button type="submit" class="btn btn-primary w-100 fw-bold">Inviter le membre</button></div>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>

<?php endif; ?>