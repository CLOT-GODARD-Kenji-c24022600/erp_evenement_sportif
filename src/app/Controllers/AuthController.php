<?php

/**
 * YES - Your Event Solution
 *
 * ERP évènementiel
 *
 * @file AuthController.php
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.1
 * @since 2026
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Models\UserModel;
use Core\Router;
use Core\Security;
use Core\Session;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Contrôleur d'authentification.
 *
 * Gère : connexion, inscription, demande de réinitialisation de mot de passe,
 * réinitialisation via token, et changement de mot de passe utilisateur connecté.
 */
class AuthController
{
    private UserModel $userModel;

    /**
     * @param UserModel $userModel Modèle utilisateur injecté.
     */
    public function __construct(UserModel $userModel)
    {
        $this->userModel = $userModel;
    }

    /**
     * Authentifie un utilisateur.
     *
     * Met à jour la session en cas de succès et redirige vers le dashboard.
     *
     * @param string $email    Adresse email soumise.
     * @param string $password Mot de passe soumis.
     * @return string|null Message d'erreur, ou null en cas de succès (redirection effectuée).
     */
    public function login(string $email, string $password): ?string
    {
        $email = Security::sanitizeString($email);

        if (!Security::isValidEmail($email)) {
            return 'Adresse email invalide.';
        }

        $user = $this->userModel->findByEmail($email);

        if ($user === null || !password_verify($password, $user['password'])) {
            return 'Identifiants incorrects.';
        }

        if ($user['statut'] === 'en_attente') {
            return 'Votre compte est en cours de validation par un administrateur.';
        }

        if ($user['statut'] === 'rejete') {
            return "L'accès vous a été refusé.";
        }

        Session::set('user_id',   $user['id']);
        Session::set('user_role', $user['role']);
        Session::set('user_nom',  trim(($user['prenom'] ?? '') . ' ' . $user['nom']));

        // URL absolue pour éviter les redirections relatives cassées (ex: /login?page=dashboard)
        Router::redirect('/dashboard');
    }

    /**
     * Inscrit un nouvel utilisateur avec statut "en_attente".
     *
     * @param string $nom             Nom de famille.
     * @param string $prenom          Prénom.
     * @param string $email           Adresse email.
     * @param string $password        Mot de passe choisi.
     * @param string $confirmPassword Confirmation du mot de passe.
     * @return array{status: string, message: string}
     */
    public function register(
        string $nom,
        string $prenom,
        string $email,
        string $password,
        string $confirmPassword
    ): array {
        $nom    = Security::sanitizeString($nom);
        $prenom = Security::sanitizeString($prenom);
        $email  = Security::sanitizeString($email);

        if (!Security::isValidEmail($email)) {
            return ['status' => 'error', 'message' => 'Adresse email invalide.'];
        }

        if ($password !== $confirmPassword) {
            return ['status' => 'error', 'message' => 'Les mots de passe ne correspondent pas.'];
        }

        if (strlen($password) < 8) {
            return ['status' => 'error', 'message' => 'Le mot de passe doit contenir au moins 8 caractères.'];
        }

        if ($this->userModel->emailExists($email)) {
            return ['status' => 'error', 'message' => 'Cette adresse email est déjà utilisée.'];
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        if ($this->userModel->create($nom, $prenom, $email, $hash)) {
            return [
                'status'  => 'success',
                'message' => 'Votre demande a bien été envoyée. Un administrateur doit valider votre compte.',
            ];
        }

        return ['status' => 'error', 'message' => "Une erreur est survenue lors de l'inscription."];
    }

    /**
     * Envoie un email de réinitialisation de mot de passe.
     *
     * @param string $email Adresse email de l'utilisateur.
     * @return array{status: string, message: string}
     */
    public function requestReset(string $email): array
    {
        $email = Security::sanitizeString($email);

        $user = $this->userModel->findByEmail($email);

        // Réponse ambiguë pour éviter la divulgation d'emails
        if ($user === null) {
            return ['status' => 'success', 'message' => 'Si ce compte existe, un mail a été envoyé.'];
        }

        $token   = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $this->userModel->setResetToken((int) $user['id'], $token, $expires);

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp-erp-evenement.alwaysdata.net';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'erp-evenement@alwaysdata.net';
            $mail->Password   = '2026Erp2026*';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer'       => false,
                    'verify_peer_name'  => false,
                    'allow_self_signed' => true,
                ],
            ];

            $mail->setFrom('erp-evenement@alwaysdata.net', 'YES - Your Event Solution');
            $mail->addAddress($email);

            $link = 'http://localhost:8080/reset_password?token=' . $token;

            $mail->isHTML(true);
            $mail->Subject = 'Réinitialisation de votre mot de passe';
            $mail->Body    = "Bonjour " . Security::escape($user['prenom']) . ",<br><br>"
                           . "Cliquez sur le lien suivant pour modifier votre mot de passe : "
                           . "<a href='{$link}'>{$link}</a><br><br>"
                           . "Ce lien expirera dans 1 heure.";

            $mail->send();

            return ['status' => 'success', 'message' => 'Le lien a été envoyé sur votre messagerie.'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => "Erreur d'envoi : {$mail->ErrorInfo}"];
        }
    }

    /**
     * Réinitialise le mot de passe via un token valide.
     *
     * @param string $token           Token de réinitialisation.
     * @param string $password        Nouveau mot de passe.
     * @param string $confirmPassword Confirmation du nouveau mot de passe.
     * @return array{status: string, message: string}
     */
    public function resetPassword(string $token, string $password, string $confirmPassword): array
    {
        if ($password !== $confirmPassword) {
            return ['status' => 'error', 'message' => 'Les mots de passe ne correspondent pas.'];
        }

        if (strlen($password) < 8) {
            return ['status' => 'error', 'message' => 'Le mot de passe doit contenir au moins 8 caractères.'];
        }

        $user = $this->userModel->findByResetToken($token);

        if ($user === null) {
            return ['status' => 'error', 'message' => 'Ce lien de réinitialisation est invalide.'];
        }

        if (strtotime($user['reset_expires']) < time()) {
            return ['status' => 'error', 'message' => 'Ce lien a expiré.'];
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        if ($this->userModel->updatePassword((int) $user['id'], $hash)) {
            return ['status' => 'success', 'message' => 'Votre mot de passe a été modifié. Vous pouvez vous connecter.'];
        }

        return ['status' => 'error', 'message' => 'Une erreur est survenue lors de la modification.'];
    }

    /**
     * Change le mot de passe d'un utilisateur connecté.
     *
     * @param int    $userId          Identifiant de l'utilisateur.
     * @param string $oldPassword     Ancien mot de passe.
     * @param string $newPassword     Nouveau mot de passe.
     * @param string $confirmPassword Confirmation du nouveau mot de passe.
     * @return array{status: string, message: string}
     */
    public function updatePassword(
        int $userId,
        string $oldPassword,
        string $newPassword,
        string $confirmPassword
    ): array {
        $user = $this->userModel->findById($userId);

        if ($user === null || !password_verify($oldPassword, $user['password'])) {
            return ['status' => 'error', 'message' => "L'ancien mot de passe est incorrect."];
        }

        if ($newPassword !== $confirmPassword) {
            return ['status' => 'error', 'message' => 'Les nouveaux mots de passe ne correspondent pas.'];
        }

        if (strlen($newPassword) < 8) {
            return ['status' => 'error', 'message' => 'Le mot de passe doit contenir au moins 8 caractères.'];
        }

        $hash = password_hash($newPassword, PASSWORD_DEFAULT);

        if ($this->userModel->updatePassword($userId, $hash)) {
            return ['status' => 'success', 'message' => 'Votre mot de passe a bien été mis à jour.'];
        }

        return ['status' => 'error', 'message' => 'Une erreur technique est survenue.'];
    }
}