<?php
session_start();

use Core\Database;
require_once __DIR__ . '/../Core/Database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Récupération de toutes les données (incluant sport et capacité)
    $nom_event   = htmlspecialchars(trim($_POST['nom_event'] ?? ''));
    $type_sport  = htmlspecialchars(trim($_POST['type_sport'] ?? ''));
    $description = htmlspecialchars(trim($_POST['description'] ?? ''));
    $date_debut  = htmlspecialchars(trim($_POST['date_debut'] ?? ''));
    $lieu        = htmlspecialchars(trim($_POST['lieu'] ?? ''));
    
    $date_fin_raw = trim($_POST['date_fin'] ?? '');
    $date_fin     = ($date_fin_raw !== '') ? htmlspecialchars($date_fin_raw) : null;
    
    // On force la capacité en entier (int), ou null si la case était vide
    $capacite = (isset($_POST['capacite']) && $_POST['capacite'] !== '') ? (int)$_POST['capacite'] : null;

    $erreurs = [];

    if (empty($nom_event)) {
        $erreurs[] = "Le nom de l'événement est obligatoire.";
    }
    if (empty($date_debut)) {
        $erreurs[] = "La date de début est obligatoire.";
    }
    if (!empty($date_fin) && strtotime($date_fin) < strtotime($date_debut)) {
        $erreurs[] = "La date de fin ne peut pas être antérieure à la date de début.";
    }

    if (count($erreurs) === 0) {
        try {
            $database = new Database();
            $db = $database->getConnection();
            $projet_id = 1; 

            // On ajoute sport et capacite dans la requête SQL
            $query = "INSERT INTO evenements (projet_id, nom, sport, date_debut, date_fin, lieu, capacite, description) 
                      VALUES (:projet_id, :nom, :sport, :date_debut, :date_fin, :lieu, :capacite, :description)";
            
            $stmt = $db->prepare($query);

            $stmt->execute([
                ':projet_id'   => $projet_id,
                ':nom'         => $nom_event,
                ':sport'       => $type_sport,
                ':date_debut'  => $date_debut,
                ':date_fin'    => $date_fin,
                ':lieu'        => $lieu,
                ':capacite'    => $capacite,
                ':description' => $description
            ]);

            $_SESSION['success_msg'] = "L'événement '$nom_event' a été enregistré dans la base de données !";
            header('Location: /?page=dashboard');
            exit;

        } catch (Exception $e) {
            $_SESSION['error_msg'] = "Erreur BDD : " . $e->getMessage();
            header('Location: /?page=nouvel_event');
            exit;
        }
    } else {
        $_SESSION['error_msg'] = implode("<br>", $erreurs);
        header('Location: /?page=nouvel_event');
        exit;
    }
} else {
    header('Location: /');
    exit;
}