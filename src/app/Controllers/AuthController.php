<?php
namespace App\Controllers;

use Core\Database;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class AuthController {
    
    // --- MÉTHODE DE CONNEXION ---
    public static function login(string $email, string $password): ?string {
        $db = Database::getConnection();
        
        $stmt = $db->prepare("SELECT * FROM utilisateurs WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            return "Identifiants incorrects.";
        }

        if ($user['statut'] === 'en_attente') {
            return "Votre compte est en cours de validation par un administrateur.";
        }
        if ($user['statut'] === 'rejete') {
            return "L'accès vous a été refusé.";
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_nom'] = $user['prenom'] . ' ' . $user['nom'];

        header("Location: ?page=dashboard");
        exit();
    }

    // --- MÉTHODE D'INSCRIPTION ---
    public static function register(string $nom, string $prenom, string $email, string $password, string $confirmPassword): array {
        $db = Database::getConnection();

        if ($password !== $confirmPassword) {
            return ['status' => 'error', 'message' => 'Les mots de passe ne correspondent pas.'];
        }

        $stmt = $db->prepare("SELECT id FROM utilisateurs WHERE email = :email");
        $stmt->execute(['email' => $email]);
        if ($stmt->fetch()) {
            return ['status' => 'error', 'message' => 'Cette adresse email est déjà utilisée.'];
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $insert = $db->prepare("INSERT INTO utilisateurs (email, password, nom, prenom, role, statut) VALUES (?, ?, ?, ?, 'staff', 'en_attente')");
        
        if ($insert->execute([$email, $hash, $nom, $prenom])) {
            return ['status' => 'success', 'message' => 'Votre demande a bien été envoyée. Un administrateur doit valider votre compte.'];
        }

        return ['status' => 'error', 'message' => 'Une erreur est survenue lors de l\'inscription.'];
    }

    // --- DEMANDE DE RÉINITIALISATION (VIA ALWAYSDATA) ---
    public static function requestReset(string $email): array {
        $db = Database::getConnection();
        
        $stmt = $db->prepare("SELECT id, prenom FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            return ['status' => 'success', 'message' => 'Si ce compte existe, un mail a été envoyé.'];
        }

        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

        $stmt = $db->prepare("UPDATE utilisateurs SET reset_token = ?, reset_expires = ? WHERE id = ?");
        $stmt->execute([$token, $expires, $user['id']]);

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp-erp-evenement.alwaysdata.net'; 
            $mail->SMTPAuth   = true;
            $mail->Username   = 'erp-evenement@alwaysdata.net'; 
            $mail->Password   = '2026Erp2026*'; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            $mail->setFrom('erp-evenement@alwaysdata.net', 'SportERP');
            $mail->addAddress($email);

            $link = "http://localhost:8080/?page=reset_password&token=" . $token;
            
            $mail->isHTML(true);
            $mail->Subject = 'Reinitialisation de votre mot de passe';
            $mail->Body    = "Bonjour " . $user['prenom'] . ",<br><br>Cliquez sur le lien suivant pour modifier votre mot de passe : <a href='$link'>$link</a><br><br>Ce lien expirera dans 1 heure.";

            $mail->send();
            return ['status' => 'success', 'message' => 'Le lien a été envoyé sur votre messagerie.'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => "Erreur Alwaysdata : {$mail->ErrorInfo}"];
        }
    }

    // --- MODIFICATION DU MOT DE PASSE VIA TOKEN (CORRIGÉE) ---
    public static function resetPassword(string $token, string $password, string $confirmPassword): array {
        if ($password !== $confirmPassword) {
            return ['status' => 'error', 'message' => 'Les mots de passe ne correspondent pas.'];
        }

        $db = Database::getConnection();
        
        // On récupère le token et l'expiration
        $stmt = $db->prepare("SELECT id, reset_expires FROM utilisateurs WHERE reset_token = ?");
        $stmt->execute([$token]);
        $user = $stmt->fetch();

        // Vérification d'existence
        if (!$user) {
            return ['status' => 'error', 'message' => 'Ce lien de réinitialisation est invalide.'];
        }

        // Vérification de l'expiration en PHP (plus fiable que SQL pour les fuseaux horaires)
        if (strtotime($user['reset_expires']) < time()) {
            return ['status' => 'error', 'message' => 'Ce lien a expiré.'];
        }

        // Mise à jour
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $update = $db->prepare("UPDATE utilisateurs SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
        
        if ($update->execute([$hash, $user['id']])) {
            return ['status' => 'success', 'message' => 'Votre mot de passe a été modifié avec succès. Vous pouvez vous connecter.'];
        }

        return ['status' => 'error', 'message' => 'Une erreur est survenue lors de la modification.'];
    }

    // --- CHANGEMENT DE MOT DE PASSE POUR UTILISATEUR CONNECTÉ (RAJOUT) ---
    public static function updatePassword(int $userId, string $oldPassword, string $newPassword, string $confirmPassword): array {
        $db = Database::getConnection();
        
        $stmt = $db->prepare("SELECT password FROM utilisateurs WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        // 1. On vérifie l'ancien mot de passe
        if (!password_verify($oldPassword, $user['password'])) {
            return ['status' => 'error', 'message' => 'L\'ancien mot de passe est incorrect.'];
        }

        // 2. On vérifie la correspondance des nouveaux
        if ($newPassword !== $confirmPassword) {
            return ['status' => 'error', 'message' => 'Les nouveaux mots de passe ne correspondent pas.'];
        }

        // 3. Mise à jour
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $update = $db->prepare("UPDATE utilisateurs SET password = ? WHERE id = ?");
        
        if ($update->execute([$hash, $userId])) {
            return ['status' => 'success', 'message' => 'Votre mot de passe a bien été mis à jour.'];
        }

        return ['status' => 'error', 'message' => 'Une erreur technique est survenue.'];
    }
}