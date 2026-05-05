<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$lang = $_SESSION['lang'] ?? 'fr';
$t = require __DIR__ . '/../../Language/' . $lang . '.php';
$theme = $_COOKIE['theme'] ?? 'light';
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" data-bs-theme="<?= $theme ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SportEvent ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root { 
            --sb-width: 260px; 
            --transition: all 0.3s ease; 
        }
        body.collapsed { --sb-width: 80px; }
        
        body { 
            font-family: 'Inter', sans-serif; 
            background-color: var(--bs-tertiary-bg); 
            transition: var(--transition);
            margin: 0;
            overflow-x: hidden;
        }
        
        /* Mode Sombre Harmonisé */
        [data-bs-theme="dark"] { 
            --bs-body-bg: #0f172a; 
            --bs-tertiary-bg: #1e293b; 
            --bs-body-color: #f1f5f9; 
            --bs-border-color: #334155;
        }
        
        /* Sidebar Fixe */
        .sidebar { 
            width: var(--sb-width); 
            height: 100vh; 
            position: fixed; 
            top: 0; 
            left: 0; 
            z-index: 1040; 
            transition: var(--transition); 
            background-color: #1e293b;
            overflow: hidden;
        }

        .main-content { 
            margin-left: var(--sb-width); 
            transition: var(--transition); 
            min-height: 100vh; 
            display: flex; 
            flex-direction: column; 
            width: calc(100% - var(--sb-width));
        }

        .top-bar { 
            height: 70px; 
            background: var(--bs-body-bg); 
            border-bottom: 1px solid var(--bs-border-color);
            position: sticky; 
            top: 0; 
            z-index: 1020;
        }

        /* --- SÉCURITÉ ANTI-CASSAGE ET CENTRAGE LOGO --- */

        /* 1. Cacher tous les textes immédiatement */
        body.collapsed .sb-text,
        body.collapsed .sidebar span:not(.fw-bold):not(.fs-5),
        body.collapsed .sidebar .text-muted,
        body.collapsed .sidebar .fw-bold.text-truncate,
        body.collapsed .sidebar .fs-5.fw-bold {
            display: none !important;
        }

        /* 2. Fixer le container du LOGO (Le p-4 de Bootstrap) */
        body.collapsed .sidebar .p-4.mb-2 {
            padding-left: 0 !important;
            padding-right: 0 !important;
            display: flex !important;
            justify-content: center !important;
        }

        /* 3. Forcer le logo SE au centre et supprimer sa marge me-2 */
        body.collapsed .sidebar .p-4.mb-2 a {
            justify-content: center !important;
            width: 100% !important;
            margin: 0 !important;
        }
        
        body.collapsed .sidebar .p-4.mb-2 a div.bg-primary {
            margin-right: 0 !important; /* On supprime l'espace qui le décale à gauche */
            flex-shrink: 0 !important;
        }

        /* 4. Centrer les icônes du menu */
        body.collapsed .sidebar .nav-link {
            justify-content: center !important;
            padding: 1rem 0 !important;
        }
        body.collapsed .sidebar .nav-link i, 
        body.collapsed .sidebar .nav-link span:first-child {
            margin: 0 !important;
        }

        /* 5. Centrer le profil S en bas */
        body.collapsed .sidebar .p-3.border-top {
            padding-left: 0 !important;
            padding-right: 0 !important;
            display: flex !important;
            justify-content: center !important;
        }
        body.collapsed .sidebar .p-3.border-top .ms-3 {
            display: none !important;
        }
        body.collapsed .sidebar .p-3.border-top .d-flex {
            justify-content: center !important;
            width: 100%;
        }

        #sidebarToggle i { transition: transform 0.3s; }
        body.collapsed #sidebarToggle i { transform: rotate(180deg); }
    </style>
</head>
<body class="<?= (isset($_COOKIE['sidebar']) && $_COOKIE['sidebar'] === 'collapsed') ? 'collapsed' : '' ?>">

<?php if (isset($_SESSION['user_id']) && !in_array($page, $pages_publiques)): ?>
    <div class="main-content">
        <header class="top-bar d-flex align-items-center px-4">
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
                            <input type="text" name="q" class="form-control form-control-sm border-0 bg-body-secondary px-3 shadow-none" style="width: 300px; border-radius: 0 8px 8px 0;" placeholder="Rechercher..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" required>
                        </div>
                    </form>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <a href="?page=nouvel_event" class="btn btn-primary btn-sm px-3 fw-bold shadow-sm">+ Quick Create</a>
                    <div class="btn-group border rounded-pill overflow-hidden">
                        <a href="?page=<?= $page ?>&lang=fr" class="btn btn-xs py-0 px-2 <?= $lang==='fr'?'bg-body-secondary fw-bold':'' ?>">FR</a>
                        <a href="?page=<?= $page ?>&lang=en" class="btn btn-xs py-0 px-2 <?= $lang==='en'?'bg-body-secondary fw-bold':'' ?>">EN</a>
                    </div>
                    <button id="darkModeToggle" class="btn btn-link text-body p-0 fs-5 shadow-none"><?= $theme==='dark'?'☀️':'🌙' ?></button>
                    <button class="btn btn-link text-body p-0 fs-5 position-relative shadow-none">
                        <i class="bi bi-bell"></i>
                        <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle" style="width: 10px; height: 10px;"></span>
                    </button>
                </div>
            </div>
        </header>
<?php endif; ?>