<?php

/**
 * YES - Your Event Solution
 *
 * ERP évènementiel
 *
 * @file ProfileController.php
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.0
 * @since 2026
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Models\UserModel;
use App\Controllers\AuthController;
use Core\Security;
use Core\Session;

/**
 * Contrôleur de gestion du profil utilisateur.
 *
 * Gère la mise à jour des informations personnelles,
 * le changement de mot de passe et l'upload d'avatar.
 */
class ProfileController
{
    private UserModel      $userModel;
    private AuthController $authController;

    /** @var string Répertoire de stockage des avatars */
    private const UPLOAD_DIR = __DIR__ . '/../../public/uploads/avatars/';

    /** @var string[] Extensions autorisées pour les avatars */
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif'];

    /**
     * @param UserModel      $userModel      Modèle utilisateur.
     * @param AuthController $authController Contrôleur d'authentification.
     */
    public function __construct(UserModel $userModel, AuthController $authController)
    {
        $this->userModel      = $userModel;
        $this->authController = $authController;
    }

    /**
     * Charge les données du profil et traite les actions POST.
     *
     * @param int $userId Identifiant de l'utilisateur connecté.
     * @return array{user: array, msg: string, msgType: string}
     */
    public function index(int $userId): array
    {
        $msg     = '';
        $msgType = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['update_info'])) {
                [$msg, $msgType] = $this->updateInfo($userId);
            } elseif (isset($_POST['update_password'])) {
                [$msg, $msgType] = $this->updatePassword($userId);
            } elseif (isset($_POST['update_avatar'])) {
                [$msg, $msgType] = $this->updateAvatar($userId);
            }
        }

        $user = $this->userModel->findById($userId);

        return [
            'user'    => $user ?? [],
            'msg'     => $msg,
            'msgType' => $msgType,
        ];
    }

    /**
     * Met à jour les informations personnelles.
     *
     * @param int $userId Identifiant de l'utilisateur.
     * @return array{0: string, 1: string} [message, type]
     */
    private function updateInfo(int $userId): array
    {
        $prenom          = Security::sanitizeString($_POST['prenom']          ?? '');
        $nom             = Security::sanitizeString($_POST['nom']             ?? '');
        $email           = Security::sanitizeString($_POST['email']           ?? '');
        $poste           = Security::sanitizeString($_POST['poste']           ?? '');
        $telephone       = Security::sanitizeString($_POST['telephone']       ?? '');
        $statutPresence  = Security::sanitizeString($_POST['statut_presence'] ?? 'online');

        if (empty($nom) || empty($email)) {
            return ["Le nom et l'email sont obligatoires.", 'danger'];
        }

        if (!Security::isValidEmail($email)) {
            return ['Adresse email invalide.', 'danger'];
        }

        if ($this->userModel->updateInfo($userId, $prenom, $nom, $email, $poste, $telephone, $statutPresence)) {
            Session::set('user_nom', trim("{$prenom} {$nom}"));
            return ['Vos informations ont été mises à jour.', 'success'];
        }

        return ['Une erreur est survenue.', 'danger'];
    }

    /**
     * Change le mot de passe de l'utilisateur connecté.
     *
     * @param int $userId Identifiant de l'utilisateur.
     * @return array{0: string, 1: string} [message, type]
     */
    private function updatePassword(int $userId): array
    {
        $result = $this->authController->updatePassword(
            $userId,
            (string) ($_POST['old_password']     ?? ''),
            (string) ($_POST['new_password']     ?? ''),
            (string) ($_POST['confirm_password'] ?? '')
        );

        return [$result['message'], $result['status'] === 'success' ? 'success' : 'danger'];
    }

    /**
     * Traite l'upload d'un avatar.
     *
     * @param int $userId Identifiant de l'utilisateur.
     * @return array{0: string, 1: string} [message, type]
     */
    private function updateAvatar(int $userId): array
    {
        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            return ["Aucun fichier reçu ou erreur d'upload.", 'danger'];
        }

        $fileName      = $_FILES['avatar']['name'];
        $fileTmpPath   = $_FILES['avatar']['tmp_name'];
        $fileExtension = strtolower((string) pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($fileExtension, self::ALLOWED_EXTENSIONS, true)) {
            return ['Format non autorisé. Utilisez JPG, PNG ou GIF.', 'danger'];
        }

        // Vérification MIME réelle
        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($fileTmpPath);
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif'];

        if (!in_array($mimeType, $allowedMimes, true)) {
            return ['Type de fichier non autorisé.', 'danger'];
        }

        if (!is_dir(self::UPLOAD_DIR)) {
            mkdir(self::UPLOAD_DIR, 0755, true);
        }

        $newFileName = 'avatar_' . $userId . '_' . time() . '.' . $fileExtension;
        $destPath    = self::UPLOAD_DIR . $newFileName;

        if (!move_uploaded_file($fileTmpPath, $destPath)) {
            return ['Erreur lors de la sauvegarde de l\'image.', 'danger'];
        }

        $this->userModel->updateAvatar($userId, $newFileName);
        Session::set('user_avatar', $newFileName);

        return ['Avatar mis à jour avec succès.', 'success'];
    }
}
