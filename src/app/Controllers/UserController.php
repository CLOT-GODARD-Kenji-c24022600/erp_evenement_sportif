<?php
namespace App\Controllers;

use Core\Database;

class UserController {
    
    public static function getAll(): array {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT * FROM utilisateurs ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    // Gère toutes les actions de l'admin
    public static function gererAction(int $id, string $action) {
        $db = Database::getConnection();

        if ($action === 'approuver') {
            $stmt = $db->prepare("UPDATE utilisateurs SET statut = 'approuve' WHERE id = :id");
            $stmt->execute(['id' => $id]);
        } 
        elseif ($action === 'rejeter') {
            $stmt = $db->prepare("UPDATE utilisateurs SET statut = 'rejete' WHERE id = :id");
            $stmt->execute(['id' => $id]);
        } 
        elseif ($action === 'supprimer') {
            $stmt = $db->prepare("DELETE FROM utilisateurs WHERE id = :id");
            $stmt->execute(['id' => $id]);
        }
        elseif ($action === 'promouvoir_admin') {
            $stmt = $db->prepare("UPDATE utilisateurs SET role = 'admin' WHERE id = :id");
            $stmt->execute(['id' => $id]);
        }
        elseif ($action === 'retrograder_staff') {
            $stmt = $db->prepare("UPDATE utilisateurs SET role = 'staff' WHERE id = :id");
            $stmt->execute(['id' => $id]);
        }
    }
}