<?php
session_start();

// 1. IMPORT DE LA CLASSE (Namespace)
// C'est cette ligne qui corrige l'erreur "Class Database not found"
use Core\Database;

// 2. INCLUSION DU FICHIER BDD (Chemin absolu infaillible)
require_once __DIR__ . '/../Core/Database.php';

// 3. VÉRIFICATION DE LA MÉTHODE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 4. NETTOYAGE ET SÉCURISATION DES DONNÉES (Anti-XSS)
    $nom_event   = htmlspecialchars(trim($_POST['nom_event'] ?? ''));
    $description = htmlspecialchars(trim($_POST['description'] ?? ''));
    $date_debut  = htmlspecialchars(trim($_POST['date_debut'] ?? ''));
    $lieu        = htmlspecialchars(trim($_POST['lieu'] ?? ''));
    
    // Si la date de fin est vide, on force à NULL pour la base de données
    $date_fin_raw = trim($_POST['date_fin'] ?? '');
    $date_fin     = ($date_fin_raw !== '') ? htmlspecialchars($date_fin_raw) : null;
    
    // NB: type_sport et capacite sont ignorés ici car ils n'existent pas dans la table `evenements`.

    // 5. VALIDATION CÔTÉ SERVEUR
    $erreurs = [];

    if (empty($nom_event)) {
        $erreurs[] = "Le nom de l'événement est obligatoire.";
    }
    if (empty($date_debut)) {
        $erreurs[] = "La date de début est obligatoire.";
    }
    
    // Si la date de fin est renseignée, on vérifie qu'elle n'est pas avant la date de début
    if (!empty($date_fin) && strtotime($date_fin) < strtotime($date_debut)) {
        $erreurs[] = "La date de fin ne peut pas être antérieure à la date de début.";
    }

    // 6. INSERTION DANS LA BASE DE DONNÉES
    if (count($erreurs) === 0) {
        try {
            // On instancie la connexion à la BDD
            // Si l'erreur persistait, il faudrait remplacer "new Database()" par "new \Database()"
            $database = new Database();
            $db = $database->getConnection();

            // ATTENTION : La colonne projet_id est "No Null" (obligatoire) dans ta BDD.
            // On la fixe à 1 par défaut le temps de créer un système de projets.
            $projet_id = 1; 

            // On prépare la requête SQL alignée avec ta structure phpMyAdmin
            $query = "INSERT INTO evenements (projet_id, nom, date_debut, date_fin, lieu, description) 
                      VALUES (:projet_id, :nom, :date_debut, :date_fin, :lieu, :description)";
            
            $stmt = $db->prepare($query);

            // On exécute la requête en liant nos variables
            $stmt->execute([
                ':projet_id'   => $projet_id,
                ':nom'         => $nom_event,
                ':date_debut'  => $date_debut,
                ':date_fin'    => $date_fin,
                ':lieu'        => $lieu,
                ':description' => $description
            ]);

            // Succès ! On crée le message et on redirige vers le Dashboard
            $_SESSION['success_msg'] = "L'événement '$nom_event' a été enregistré dans la base de données !";
            header('Location: /?page=dashboard');
            exit;

        } catch (Exception $e) {
            // En cas de problème de syntaxe SQL ou de connexion
            $_SESSION['error_msg'] = "Erreur BDD : " . $e->getMessage();
            header('Location: /?page=nouvel_event');
            exit;
        }
    } else {
        // S'il y a des erreurs de validation
        $_SESSION['error_msg'] = implode("<br>", $erreurs);
        header('Location: /?page=nouvel_event');
        exit;
    }

} else {
    // Si la page est accédée directement en GET
    header('Location: /');
    exit;
}