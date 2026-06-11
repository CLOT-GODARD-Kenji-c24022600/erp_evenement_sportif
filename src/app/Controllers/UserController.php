<?php

/**
 * YES - Your Event Solution
 *
 * ERP évènementiel
 *
 * @file UserController.php
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.1
 * @since 2026
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\HistoriqueModel;
use Core\Security;
use Core\Session;

/**
 * Contrôleur de gestion des utilisateurs (administration).
 *
 * Permet à un administrateur d'approuver, rejeter, promouvoir,
 * rétrograder ou supprimer des comptes utilisateurs.
 */
class UserController
{
    private UserModel $userModel;

    /**
     * @param UserModel $userModel Modèle utilisateur.
     */
    public function __construct(UserModel $userModel)
    {
        $this->userModel = $userModel;
    }

    /**
     * Retourne tous les utilisateurs (pour la liste admin).
     *
     * @return array[]
     */
    public function getAll(): array
    {
        return $this->userModel->getAll();
    }

    /**
     * Exécute une action administrative sur un utilisateur.
     *
     * @param int    $targetId Identifiant de l'utilisateur cible.
     * @param string $action   Action à effectuer.
     * @return void
     */
    public function handleAction(int $targetId, string $action): void
    {
        $currentUserId   = (int) Session::get('user_id', 0);
        $currentRole     = (string) Session::get('user_role', '');

        // On ne peut pas agir sur soi-même
        if ($targetId === $currentUserId) return;

        // Récupérer l'utilisateur cible
        $target = $this->userModel->findById($targetId);
        if (!$target) return;

        $targetRole = (string) ($target['role'] ?? 'staff');

        // Personne ne peut modifier un super_admin sauf un autre super_admin
        if ($targetRole === 'super_admin' && $currentRole !== 'super_admin') return;

        $newRole = Security::sanitizeString($_POST['new_role'] ?? '');

        // Seul le super_admin peut attribuer le rôle super_admin
        if ($newRole === 'super_admin' && $currentRole !== 'super_admin') return;

        $validActions = [
            'approuver', 'rejeter', 'supprimer',
            'promouvoir_admin', 'retrograder_staff', 'changer_role',
        ];
        if (!in_array($action, $validActions, true)) return;

        match ($action) {
            'approuver'         => $this->userModel->setStatut($targetId, 'approuve'),
            'rejeter'           => $this->userModel->setStatut($targetId, 'rejete'),
            'supprimer'         => $this->userModel->delete($targetId),
            'promouvoir_admin'  => $this->userModel->setRole($targetId, 'admin'),
            'retrograder_staff' => $this->userModel->setRole($targetId, 'staff'),
            'changer_role'      => $this->changeRole($targetId, $newRole, $currentRole),
        };
    }

    private function changeRole(int $targetId, string $newRole, string $currentRole): void
    {
        if (!array_key_exists($newRole, UserModel::ROLES)) return;

        // Seul super_admin peut donner super_admin
        if ($newRole === 'super_admin' && $currentRole !== 'super_admin') return;

        // Récupérer l'ancien rôle pour le log
        $target  = $this->userModel->findById($targetId);
        $oldRole = (string) ($target['role'] ?? 'staff');
        $nom     = trim(($target['prenom'] ?? '') . ' ' . ($target['nom'] ?? ''));

        $ok = $this->userModel->setRole($targetId, $newRole);
        if (!$ok) return;

        // Mettre à jour la session si c'est le compte connecté
        if ($targetId === (int) Session::get('user_id', 0)) {
            Session::set('user_role', $newRole);
        }

        // Logger le changement de rôle
        try {
            HistoriqueModel::log('update', 'utilisateur', $targetId, $nom, [
                'action'    => 'changement_role',
                'old_role'  => UserModel::roleLabel($oldRole),
                'new_role'  => UserModel::roleLabel($newRole),
            ]);
        } catch (\Throwable) {}
    }


/**
     * Importe des utilisateurs à partir d'un fichier CSV (Format Staff).
     *
     * @param array $file Données du fichier issu de $_FILES.
     * @return void
     */
    public function importCsv(array $file): void
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            Session::set('error_msg', "Erreur lors du téléchargement du fichier.");
            return;
        }

        // 1. Correction du fameux bug des sauts de ligne Excel/Mac
        ini_set('auto_detect_line_endings', '1');

        $handle = fopen($file['tmp_name'], 'r');
        if (!$handle) {
            Session::set('error_msg', "Impossible d'ouvrir le fichier.");
            return;
        }

        // Détection automatique du séparateur (, ou ;)
        $premiereLigne = fgets($handle);
        $delimiter = (strpos($premiereLigne ?: '', ';') !== false) ? ';' : ',';
        rewind($handle);

        $count = 0;
        $startImport = false;

        while (($data = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
            $col0 = trim(Security::sanitizeString($data[0] ?? ''));

            // 2. Intelligence : on ignore tout le haut du fichier jusqu'à trouver "Nom"
            if (!$startImport) {
                if (strtolower($col0) === 'nom') {
                    $startImport = true; // On a trouvé le tableau, on démarre au prochain tour !
                }
                continue;
            }

            // Si la ligne est vide, on saute
            if (empty($col0)) {
                continue;
            }

            // Découpage du Nom Complet (ex: "Elie Kaczmar")
            $parts = explode(' ', $col0, 2);
            $prenom = $parts[0]; 
            $nom = $parts[1] ?? '';

            // Gestion de l'email manquant
            $email = isset($data[2]) ? trim(Security::sanitizeString($data[2])) : '';
            if (empty($email)) {
                $cleanPrenom = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($prenom));
                $cleanNom = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($nom));
                $email = $cleanPrenom . '.' . $cleanNom . '@yes-erp.local';
            }

            $tempPassword = password_hash('WelcomeYES2026!', PASSWORD_BCRYPT);

            try {
                // Évite les doublons si tu importes 2 fois le même fichier
                if ($this->userModel->emailExists($email)) {
                    continue; 
                }

                // Création du compte
                if ($this->userModel->create($nom, $prenom, $email, $tempPassword)) {
                    $newUser = $this->userModel->findByEmail($email);
                    if ($newUser) {
                        $userId = (int) $newUser['id'];
                        $this->userModel->setRole($userId, 'staff');
                        $this->userModel->setStatut($userId, 'approuve');
                        $count++;
                    }
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        fclose($handle);

        if ($count > 0) {
            Session::set('success_msg', "$count membres du staff ont été importés avec succès !");
        } else {
            Session::set('error_msg', "Aucune donnée n'a été importée. Vérifiez que la première colonne s'appelle bien 'Nom'.");
        }
    }
}