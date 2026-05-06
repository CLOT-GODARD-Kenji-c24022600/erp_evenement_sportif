<?php
session_start();

use Core\Database;
require_once __DIR__ . '/../Core/Database.php';

// On vérifie que la requête est bien un POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // On récupère l'ID et l'action (update ou delete)
    $id_event = (int)($_POST['id'] ?? 0);
    $action   = $_POST['action'] ?? '';

    // Sécurité : si pas d'ID valide, on dégage
    if ($id_event <= 0) {
        header('Location: /?page=dashboard');
        exit;
    }

    try {
        $database = new Database();
        $db = $database->getConnection();

        // -----------------------------------------
        // ACTION 1 : SUPPRESSION
        // -----------------------------------------
        if ($action === 'delete') {
            $stmt = $db->prepare("DELETE FROM evenements WHERE id = :id");
            $stmt->execute([':id' => $id_event]);
            
            $_SESSION['success_msg'] = "L'événement a été supprimé avec succès.";
        } 
        // -----------------------------------------
        // ACTION 2 : MISE À JOUR
        // -----------------------------------------
        elseif ($action === 'update') {
            // Nettoyage des données
            $nom_event   = htmlspecialchars(trim($_POST['nom_event'] ?? ''));
            $type_sport  = htmlspecialchars(trim($_POST['type_sport'] ?? ''));
            $description = htmlspecialchars(trim($_POST['description'] ?? ''));
            $date_debut  = htmlspecialchars(trim($_POST['date_debut'] ?? ''));
            $lieu        = htmlspecialchars(trim($_POST['lieu'] ?? ''));
            
            $date_fin_raw = trim($_POST['date_fin'] ?? '');
            $date_fin     = ($date_fin_raw !== '') ? htmlspecialchars($date_fin_raw) : null;
            
            $capacite = (isset($_POST['capacite']) && $_POST['capacite'] !== '') ? (int)$_POST['capacite'] : null;

            // Petite validation rapide
            if (empty($nom_event) || empty($date_debut)) {
                $_SESSION['error_msg'] = "Le nom et la date de début sont obligatoires.";
                header("Location: /?page=gerer_event&id=$id_event");
                exit;
            }

            // Requête d'UPDATE
            $query = "UPDATE evenements SET 
                        nom = :nom, 
                        sport = :sport, 
                        date_debut = :date_debut, 
                        date_fin = :date_fin, 
                        lieu = :lieu, 
                        capacite = :capacite, 
                        description = :description 
                      WHERE id = :id";
            
            $stmt = $db->prepare($query);
            $stmt->execute([
                ':nom'         => $nom_event,
                ':sport'       => $type_sport,
                ':date_debut'  => $date_debut,
                ':date_fin'    => $date_fin,
                ':lieu'        => $lieu,
                ':capacite'    => $capacite,
                ':description' => $description,
                ':id'          => $id_event
            ]);

            $_SESSION['success_msg'] = "L'événement '$nom_event' a été mis à jour avec succès !";
        }

        // Si tout s'est bien passé, on retourne sur le dashboard
        header('Location: /?page=dashboard');
        exit;

    } catch (Exception $e) {
        $_SESSION['error_msg'] = "Erreur BDD : " . $e->getMessage();
        header("Location: /?page=gerer_event&id=$id_event");
        exit;
    }

} else {
    // Si on accède au fichier directement via l'URL
    header('Location: /?page=dashboard');
    exit;
}