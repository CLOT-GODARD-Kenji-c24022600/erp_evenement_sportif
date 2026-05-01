<?php
session_start();

// 1. VÉRIFICATION DE LA MÉTHODE
// On s'assure que la page a bien été appelée par la soumission du formulaire (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 2. NETTOYAGE ET SÉCURISATION DES DONNÉES (Anti-XSS)
    // On utilise htmlspecialchars() pour empêcher l'exécution de code HTML/JavaScript malveillant
    $nom_event   = htmlspecialchars(trim($_POST['nom_event'] ?? ''));
    $type_sport  = htmlspecialchars(trim($_POST['type_sport'] ?? ''));
    $description = htmlspecialchars(trim($_POST['description'] ?? ''));
    $date_debut  = htmlspecialchars(trim($_POST['date_debut'] ?? ''));
    $date_fin    = htmlspecialchars(trim($_POST['date_fin'] ?? ''));
    $lieu        = htmlspecialchars(trim($_POST['lieu'] ?? ''));
    
    // Pour la capacité, on force le type en entier (integer)
    $capacite    = isset($_POST['capacite']) && $_POST['capacite'] !== '' ? (int)$_POST['capacite'] : null;

    // 3. VALIDATION CÔTÉ SERVEUR
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

    // 4. GESTION DU RÉSULTAT
    if (count($erreurs) === 0) {
        // --- C'est ici que tu mettras ta requête PDO plus tard ---
        
        // On simule un succès
        $_SESSION['success_msg'] = "L'événement '$nom_event' a été créé avec succès !";
        
        // On redirige vers le tableau de bord
        header('Location: /');
        exit;
    } else {
        // S'il y a des erreurs, on les stocke en session et on renvoie vers le formulaire
        $_SESSION['error_msg'] = implode("<br>", $erreurs);
        header('Location: /nouvel_event.php');
        exit;
    }

} else {
    // Si quelqu'un essaie d'accéder à ce fichier directement via l'URL (GET)
    header('Location: /');
    exit;
}