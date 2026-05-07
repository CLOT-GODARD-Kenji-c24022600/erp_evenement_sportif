<?php
use Core\Database;
require_once __DIR__ . '/../../Core/Database.php';

// 1. On vérifie qu'un ID est bien présent dans l'URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: /?page=dashboard');
    exit;
}

$id_event = (int)$_GET['id'];
$event = null;

// 2. On récupère les données de cet événement spécifique
try {
    $database = new Database();
    $db = $database->getConnection();
    
    $stmt = $db->prepare("SELECT * FROM evenements WHERE id = :id");
    $stmt->execute([':id' => $id_event]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Si l'événement n'existe pas en BDD, on redirige
    if (!$event) {
        $_SESSION['error_msg'] = "Événement introuvable.";
        header('Location: /?page=dashboard');
        exit;
    }
} catch (Exception $e) {
    echo "Erreur BDD : " . $e->getMessage();
    exit;
}
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold text-body mb-0">Gérer l'événement</h1>
            <p class="text-body-secondary mb-0">Modification de l'événement #<?= $event['id'] ?></p>
        </div>
        <a href="/?page=dashboard" class="btn btn-outline-secondary shadow-sm">Retour</a>
    </div>

    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body p-4">
            <!-- Le formulaire pointera vers un nouveau fichier de traitement -->
            <form action="/traitement_gerer_event.php" method="POST">
                
                <!-- Champ caché très important pour transmettre l'ID au traitement PHP -->
                <input type="hidden" name="id" value="<?= $event['id'] ?>">
                
                <!-- Champ caché pour savoir si on clique sur Modifier ou Supprimer -->
                <input type="hidden" name="action" id="form_action" value="update">

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nom de l'événement</label>
                        <input type="text" name="nom_event" class="form-control" value="<?= htmlspecialchars($event['nom']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Type de sport</label>
                        <input type="text" name="type_sport" class="form-control" value="<?= htmlspecialchars($event['sport'] ?? '') ?>">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Lieu</label>
                        <input type="text" name="lieu" class="form-control" value="<?= htmlspecialchars($event['lieu']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Capacité</label>
                        <input type="number" name="capacite" class="form-control" value="<?= htmlspecialchars($event['capacite'] ?? '') ?>">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Date de début</label>
                        <!-- On formate la date pour l'input datetime-local -->
                        <input type="datetime-local" name="date_debut" class="form-control" value="<?= date('Y-m-d\TH:i', strtotime($event['date_debut'])) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Date de fin</label>
                        <input type="datetime-local" name="date_fin" class="form-control" value="<?= $event['date_fin'] ? date('Y-m-d\TH:i', strtotime($event['date_fin'])) : '' ?>">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($event['description'] ?? '') ?></textarea>
                </div>

                <!-- Boutons d'action -->
                <div class="d-flex justify-content-between">
                    <button type="submit" onclick="document.getElementById('form_action').value='delete'; return confirm('Es-tu sûr de vouloir supprimer cet événement définitivement ?');" class="btn btn-danger">
                        <i class="bi bi-trash me-2"></i>Supprimer
                    </button>
                    <button type="submit" onclick="document.getElementById('form_action').value='update';" class="btn btn-primary px-4">
                        <i class="bi bi-save me-2"></i>Mettre à jour
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>