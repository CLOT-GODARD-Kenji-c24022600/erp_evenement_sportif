<?php
require_once __DIR__ . '/../../vendor/autoload.php';
use Core\Database;

$db = Database::getConnection();
$email = 'erp.sportif.2026@outlook.com';
$password = 'Erp2026Erp*';
$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $db->prepare("INSERT INTO utilisateurs (email, password, nom, prenom, role, statut) VALUES (?, ?, 'Admin', 'Super', 'admin', 'approuve')");
$stmt->execute([$email, $hash]);

echo "✅ Compte Admin créé avec succès ! Tu peux supprimer ce fichier setup.php.";