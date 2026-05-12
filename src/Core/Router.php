<?php

/**
 * YES - Your Event Solution
 *
 * @file Router.php
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.4
 * @since 2026
 */

declare(strict_types=1);

namespace Core;

use App\Models\UserModel;
use App\Models\EventModel;
use App\Models\TodoModel;
use App\Models\SearchModel;
use App\Models\QuickCreateModel;
use App\Models\ProjectModel;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\EventController;
use App\Controllers\TodoController;
use App\Controllers\ProfileController;
use App\Controllers\UserController;
use App\Controllers\SearchController;
use App\Controllers\QuickCreateController;
use App\Controllers\ProjectController;

class Router
{
    private const PUBLIC_PAGES = [
        'login',
        'inscription',
        'forgot_password',
        'reset_password',
    ];

    private const ALLOWED_PAGES = [
        'login',
        'inscription',
        'dashboard',
        'nouvel_event',
        'annuaire',
        'utilisateurs',
        'forgot_password',
        'reset_password',
        'profil',
        'staff',
        'recherche',
        'change_lang',
        'gerer_event',
        'logout',
        'projets',
        'projet_detail',
        'ajax_search',
        'ajax_presence',
    ];

    public static function dispatch(): void
    {
        $page  = self::resolvePage();
        $lang  = Bootstrap::getLang();
        $theme = Bootstrap::getTheme();
        $t     = Bootstrap::loadTranslations($lang);

        self::handleSpecialRoutes($page, $lang);

        if (in_array($page, self::PUBLIC_PAGES, true)) {
            self::dispatchPublic($page, $t, $theme);
            return;
        }

        if (!Session::has('user_id')) {
            self::redirect('/login');
        }

        self::dispatchApp($page, $t, $lang, $theme);
    }

    public static function redirect(string $url): never
    {
        // Convertir les anciennes URLs /?page=xxx en /xxx
        if (str_starts_with($url, '/?page=')) {
            $url = '/' . substr($url, 7);
            $url = preg_replace('/&/', '?', $url, 1);
        }
        header('Location: ' . $url);
        exit();
    }

    private static function resolvePage(): string
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
        $uri = trim($uri, '/');

        $uriMap = [
            ''                => 'dashboard',
            'dashboard'       => 'dashboard',
            'staff'           => 'staff',
            'projets'         => 'projets',
            'annuaire'        => 'annuaire',
            'profil'          => 'profil',
            'utilisateurs'    => 'utilisateurs',
            'nouvel_event'    => 'nouvel_event',
            'recherche'       => 'recherche',
            'login'           => 'login',
            'inscription'     => 'inscription',
            'logout'          => 'logout',
            'forgot_password' => 'forgot_password',
            'reset_password'  => 'reset_password',
            'gerer_event'     => 'gerer_event',
            'projet_detail'   => 'projet_detail',
            'ajax_search'     => 'ajax_search',
            'ajax_presence'   => 'ajax_presence',
            'change_lang'     => 'change_lang',
        ];

        if (isset($uriMap[$uri])) {
            return $uriMap[$uri];
        }

        $page = Security::sanitizeString($_GET['page'] ?? 'dashboard');
        if (in_array($page, self::ALLOWED_PAGES, true)) {
            return $page;
        }

        return 'dashboard';
    }

    private static function handleSpecialRoutes(string $page, string $lang): void
    {
        if ($page === 'logout') {
            Session::destroy();
            self::redirect('/login');
        }

        if ($page === 'change_lang') {
            $newLang = in_array($_GET['lang'] ?? '', ['fr', 'en'], true)
                ? $_GET['lang']
                : 'fr';
            $return = Security::sanitizeString($_GET['return'] ?? 'dashboard');
            Session::set('lang', $newLang);
            self::redirect("/{$return}");
        }

        if ($page === 'ajax_search') {
            if (!Session::has('user_id')) {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
                exit();
            }

            header('Content-Type: application/json; charset=UTF-8');

            $q = Security::sanitizeString($_GET['q'] ?? '');

            if (strlen($q) < 2) {
                echo json_encode(['projets' => [], 'events' => [], 'staff' => []]);
                exit();
            }

            $model = new SearchModel();

            echo json_encode([
                'projets' => array_map(fn($p) => [
                    'id'  => (int) $p['id'],
                    'nom' => $p['nom'],
                    'sub' => ucfirst(str_replace('_', ' ', $p['statut'] ?? '')),
                ], array_slice($model->searchProjets($q), 0, 5)),

                'events' => array_map(fn($e) => [
                    'id'  => (int) $e['id'],
                    'nom' => $e['nom'],
                    'sub' => !empty($e['date_debut']) ? date('d/m/Y', strtotime($e['date_debut'])) : '',
                ], array_slice($model->searchEvents($q), 0, 5)),

                'staff' => array_map(fn($u) => [
                    'nom'         => $u['nom'],
                    'prenom'      => $u['prenom'] ?? '',
                    'nom_famille' => $u['nom'],
                    'sub'         => $u['poste'] ?? '',
                ], array_slice($model->searchStaff($q), 0, 4)),
            ], JSON_UNESCAPED_UNICODE);

            exit();
        }

        if ($page === 'ajax_presence') {
            if (!Session::has('user_id')) {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
                exit();
            }

            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode((new UserModel())->getPresence(), JSON_UNESCAPED_UNICODE);
            exit();
        }
    }

    private static function dispatchPublic(string $page, array $t, string $theme): void
    {
        $authController = new AuthController(new UserModel());

        $message = '';
        $type    = '';
        $erreur  = '';
        $token   = Security::sanitizeString($_GET['token'] ?? '');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            switch ($page) {
                case 'login':
                    $erreur = $authController->login(
                        (string) ($_POST['email']    ?? ''),
                        (string) ($_POST['password'] ?? '')
                    ) ?? '';
                    break;

                case 'inscription':
                    $result  = $authController->register(
                        (string) ($_POST['nom']              ?? ''),
                        (string) ($_POST['prenom']           ?? ''),
                        (string) ($_POST['email']            ?? ''),
                        (string) ($_POST['password']         ?? ''),
                        (string) ($_POST['confirm_password'] ?? '')
                    );
                    $message = $result['message'];
                    $type    = $result['status'] === 'success' ? 'success' : 'danger';
                    break;

                case 'forgot_password':
                    $result  = $authController->requestReset((string) ($_POST['email'] ?? ''));
                    $message = $result['message'];
                    $type    = $result['status'] === 'success' ? 'success' : 'danger';
                    break;

                case 'reset_password':
                    $result  = $authController->resetPassword(
                        $token,
                        (string) ($_POST['password']         ?? ''),
                        (string) ($_POST['confirm_password'] ?? '')
                    );
                    $message = $result['message'];
                    $type    = $result['status'] === 'success' ? 'success' : 'danger';
                    break;
            }
        }

        $viewMap = [
            'login'           => __DIR__ . '/../app/Views/auth/login.php',
            'inscription'     => __DIR__ . '/../app/Views/auth/inscription.php',
            'forgot_password' => __DIR__ . '/../app/Views/auth/forgot_password.php',
            'reset_password'  => __DIR__ . '/../app/Views/auth/reset_password.php',
        ];

        Renderer::renderPublic(
            $viewMap[$page],
            compact('message', 'type', 'erreur', 'token', 't'),
            $theme,
            '<script src="assets/js/auth.js"></script>'
        );
    }

    private static function dispatchApp(string $page, array $t, string $lang, string $theme): void
    {
        $userModel = new UserModel();
        $userId    = (int) Session::get('user_id');
        $isAdmin   = Session::get('user_role') === 'admin';

        try {
            $userModel->updateLastActivity($userId);

            $statut = $userModel->getStatut($userId);
            if ($statut === null || $statut === 'rejete') {
                Session::destroy();
                self::redirect('/login');
            }
        } catch (\Exception $e) {
        }

        $currentUser   = $userModel->findById($userId);
        $sidebarNom    = Session::get('user_nom', 'Utilisateur');
        $sidebarAvatar = $currentUser['avatar'] ?? null;
        $dbStatus      = 'online';

        try {
            Database::getConnection();
        } catch (\Exception $e) {
            $dbStatus = 'offline';
        }

        $notifications = [];
        try {
            $notifications = (new EventModel())->getUpcoming(3);
        } catch (\Exception $e) {
        }

        $qcResult = (new QuickCreateController(new QuickCreateModel()))->handle();
        $qcMsg    = $qcResult['msg']  ?? '';
        $qcType   = $qcResult['type'] ?? 'success';

        $common = compact(
            'page', 't', 'lang', 'theme', 'isAdmin',
            'sidebarNom', 'sidebarAvatar', 'dbStatus',
            'notifications', 'qcMsg', 'qcType'
        );

        switch ($page) {

            case 'dashboard':
                $ctrl     = new DashboardController(
                    new EventModel(),
                    new TodoModel(),
                    new TodoController(new TodoModel())
                );
                $viewData = $ctrl->index();
                Renderer::renderApp(
                    __DIR__ . '/../app/Views/dashboard/dashboard.php',
                    array_merge($common, $viewData),
                    '<link rel="stylesheet" href="assets/css/dashboard.css">',
                    '<script src="assets/js/dashboard.js"></script>'
                );
                break;

            case 'nouvel_event':
                $projetsSimple = [];
                try {
                    $projetsSimple = (new ProjectModel())->getAllSimple();
                } catch (\Exception $e) {
                }
                Renderer::renderApp(
                    __DIR__ . '/../app/Views/events/nouvel_event.php',
                    array_merge($common, ['projets' => $projetsSimple]),
                    '',
                    '<script src="assets/js/event.js"></script>'
                );
                break;

            case 'gerer_event':
                $ctrl  = new EventController(new EventModel());
                $id    = Security::sanitizeInt($_GET['id'] ?? 0);
                $event = $ctrl->getForEdit($id);

                if ($event === null) {
                    self::redirect('/dashboard');
                }

                Renderer::renderApp(
                    __DIR__ . '/../app/Views/events/gerer_event.php',
                    array_merge($common, ['event' => $event])
                );
                break;

            case 'annuaire':
                Renderer::renderApp(
                    __DIR__ . '/../app/Views/directory/annuaire.php',
                    $common
                );
                break;

            case 'staff':
                $staffMembers = [];
                try {
                    $staffMembers = $userModel->getAllApproved();
                } catch (\Exception $e) {
                }
                Renderer::renderApp(
                    __DIR__ . '/../app/Views/staff/staff.php',
                    array_merge($common, ['staffMembers' => $staffMembers]),
                    '<link rel="stylesheet" href="assets/css/staff.css">',
                    '<script src="assets/js/staff.js"></script>'
                );
                break;

            case 'profil':
                $ctrl     = new ProfileController($userModel, new AuthController($userModel));
                $viewData = $ctrl->index($userId);
                Renderer::renderApp(
                    __DIR__ . '/../app/Views/profile/profil.php',
                    array_merge($common, $viewData),
                    '<link rel="stylesheet" href="assets/css/staff.css">',
                    '<script src="assets/js/profile.js"></script>'
                );
                break;

            case 'utilisateurs':
                if (!$isAdmin) {
                    self::redirect('/dashboard');
                }
                $ctrl = new UserController($userModel);

                if ($_SERVER['REQUEST_METHOD'] === 'POST'
                    && isset($_POST['action'], $_POST['user_id'])
                ) {
                    $ctrl->handleAction(
                        Security::sanitizeInt($_POST['user_id']),
                        Security::sanitizeString($_POST['action'])
                    );
                    self::redirect('/utilisateurs');
                }

                Renderer::renderApp(
                    __DIR__ . '/../app/Views/users/utilisateurs.php',
                    array_merge($common, ['users' => $ctrl->getAll()])
                );
                break;

            case 'recherche':
                $ctrl     = new SearchController(new SearchModel());
                $viewData = $ctrl->search($_GET['q'] ?? '');
                Renderer::renderApp(
                    __DIR__ . '/../app/Views/search/recherche.php',
                    array_merge($common, $viewData),
                    '<link rel="stylesheet" href="assets/css/staff.css">'
                );
                break;

            case 'projets':
                $ctrl     = new ProjectController(new ProjectModel());
                $viewData = $ctrl->index();
                Renderer::renderApp(
                    __DIR__ . '/../app/Views/projects/projets.php',
                    array_merge($common, $viewData),
                    '<link rel="stylesheet" href="assets/css/projects.css">',
                    '<script src="assets/js/projects.js"></script>'
                );
                break;

            case 'projet_detail':
                $ctrl = new ProjectController(new ProjectModel());
                $id   = Security::sanitizeInt($_GET['id'] ?? 0);
                $data = $ctrl->detail($id);

                if ($data === null) {
                    self::redirect('/projets');
                }

                Renderer::renderApp(
                    __DIR__ . '/../app/Views/projects/projet_detail.php',
                    array_merge($common, $data),
                    '<link rel="stylesheet" href="assets/css/projects.css">',
                    '<script src="assets/js/projects.js"></script>'
                );
                break;

            default:
                self::redirect('/dashboard');
        }
    }
}