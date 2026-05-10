<?php

/**
 * YES - Your Event Solution
 *
 * @file Router.php
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.0
 * @since 2026
 */

declare(strict_types=1);

namespace Core;

use App\Models\UserModel;
use App\Models\EventModel;
use App\Models\TodoModel;
use App\Models\SearchModel;
use App\Models\QuickCreateModel;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\EventController;
use App\Controllers\TodoController;
use App\Controllers\ProfileController;
use App\Controllers\UserController;
use App\Controllers\SearchController;
use App\Controllers\QuickCreateController;

/**
 * Routeur principal de l'application.
 *
 * Responsabilités :
 * - Gérer les routes spéciales (logout, change_lang).
 * - Appliquer la liste blanche des pages autorisées.
 * - Appliquer le garde d'authentification.
 * - Dispatcher vers le bon controller.
 * - Déléguer le rendu à Renderer.
 */
class Router
{
    /** @var string[] Pages accessibles sans être connecté */
    private const PUBLIC_PAGES = [
        'login',
        'inscription',
        'forgot_password',
        'reset_password',
    ];

    /** @var string[] Toutes les pages autorisées (liste blanche) */
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
    ];

    /**
     * Point d'entrée principal du routeur.
     * Résout la page, applique les guards, dispatche vers le controller.
     *
     * @return void
     */
    public static function dispatch(): void
    {
        $page  = self::resolvePage();
        $lang  = Bootstrap::getLang();
        $theme = Bootstrap::getTheme();
        $t     = Bootstrap::loadTranslations($lang);

        // ── Routes spéciales (pas de vue) ─────────────────────
        self::handleSpecialRoutes($page, $lang);

        // ── Pages publiques ────────────────────────────────────
        if (in_array($page, self::PUBLIC_PAGES, true)) {
            self::dispatchPublic($page, $t, $theme);
            return;
        }

        // ── Guard : authentification requise ──────────────────
        if (!Session::has('user_id')) {
            self::redirect('/?page=login');
        }

        // ── Pages authentifiées ────────────────────────────────
        self::dispatchApp($page, $t, $lang, $theme);
    }

    /**
     * Redirige vers une URL et termine l'exécution.
     *
     * @param string $url URL de destination.
     * @return never
     */
    public static function redirect(string $url): never
    {
        header('Location: ' . $url);
        exit();
    }

    // ════════════════════════════════════════════════════════
    // Méthodes privées
    // ════════════════════════════════════════════════════════

    /**
     * Résout la page courante avec liste blanche.
     *
     * @return string
     */
    private static function resolvePage(): string
    {
        $page = Security::sanitizeString($_GET['page'] ?? 'dashboard');

        if (!in_array($page, self::ALLOWED_PAGES, true)) {
            return 'dashboard';
        }

        return $page;
    }

    /**
     * Gère les routes sans vue (logout, change_lang).
     *
     * @param string $page Page courante.
     * @param string $lang Langue active.
     * @return void
     */
    private static function handleSpecialRoutes(string $page, string $lang): void
    {
        if ($page === 'logout') {
            Session::destroy();
            self::redirect('/?page=login');
        }

        if ($page === 'change_lang') {
            $newLang = in_array($_GET['lang'] ?? '', ['fr', 'en'], true)
                ? $_GET['lang']
                : 'fr';
            $return = Security::sanitizeString($_GET['return'] ?? 'dashboard');
            Session::set('lang', $newLang);
            self::redirect("/?page={$return}");
        }
    }

    /**
     * Dispatche les pages publiques (pas de layout app).
     *
     * @param string $page  Page courante.
     * @param array  $t     Traductions.
     * @param string $theme Thème actif.
     * @return void
     */
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

    /**
     * Dispatche les pages authentifiées (layout app complet).
     *
     * @param string $page  Page courante.
     * @param array  $t     Traductions.
     * @param string $lang  Langue active.
     * @param string $theme Thème actif.
     * @return void
     */
    private static function dispatchApp(string $page, array $t, string $lang, string $theme): void
    {
        $userModel = new UserModel();
        $userId    = (int) Session::get('user_id');
        $isAdmin   = Session::get('user_role') === 'admin';

        // Mise à jour dernière activité + vérification statut
        try {
            $userModel->updateLastActivity($userId);

            if (!$isAdmin) {
                $statut = $userModel->getStatut($userId);
                if ($statut === 'rejete') {
                    Session::destroy();
                    self::redirect('/?page=login');
                }
            }
        } catch (\Exception $e) {
            // BDD momentanément indisponible : on laisse passer
        }

        // Variables communes à toutes les vues authentifiées
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

        // Quick-create (modales header)
        $qcResult = (new QuickCreateController(new QuickCreateModel()))->handle();
        $qcMsg    = $qcResult['msg']  ?? '';
        $qcType   = $qcResult['type'] ?? 'success';

        // Variables communes injectées dans toutes les vues
        $common = compact(
            'page', 't', 'lang', 'theme', 'isAdmin',
            'sidebarNom', 'sidebarAvatar', 'dbStatus',
            'notifications', 'qcMsg', 'qcType'
        );

        // Dispatch par page
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
                Renderer::renderApp(
                    __DIR__ . '/../app/Views/events/nouvel_event.php',
                    $common,
                    '',
                    '<script src="assets/js/events.js"></script>'
                );
                break;

            case 'gerer_event':
                $ctrl  = new EventController(new EventModel());
                $id    = Security::sanitizeInt($_GET['id'] ?? 0);
                $event = $ctrl->getForEdit($id);

                if ($event === null) {
                    self::redirect('/?page=dashboard');
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
                    self::redirect('/?page=dashboard');
                }
                $ctrl = new UserController($userModel);

                if ($_SERVER['REQUEST_METHOD'] === 'POST'
                    && isset($_POST['action'], $_POST['user_id'])
                ) {
                    $ctrl->handleAction(
                        Security::sanitizeInt($_POST['user_id']),
                        Security::sanitizeString($_POST['action'])
                    );
                    self::redirect('/?page=utilisateurs');
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

            default:
                self::redirect('/?page=dashboard');
        }
    }
}