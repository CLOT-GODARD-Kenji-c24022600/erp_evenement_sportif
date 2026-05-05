<?php
// --- 1. LOGIQUE PHP ---
use Core\Database;

$db = Database::getConnection();
$user_id = $_SESSION['user_id'];
$msg = '';
$msgType = '';
$uploadDir = __DIR__ . '/../../public/uploads/avatars/';

// --- TRAITEMENT DES FORMULAIRES ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // ACTION 1 : Mise à jour des infos
    if (isset($_POST['update_info'])) {
        $prenom = trim($_POST['prenom'] ?? '');
        $nom = trim($_POST['nom']);
        $email = trim($_POST['email']);
        $poste = trim($_POST['poste'] ?? '');
        $telephone = trim($_POST['telephone'] ?? '');
        $statut_presence = $_POST['statut_presence'] ?? 'online'; // NOUVEAU : On récupère le statut

        if (!empty($nom) && !empty($email)) {
            // NOUVEAU : On met à jour statut_presence dans la BDD
            $stmt = $db->prepare("UPDATE utilisateurs SET prenom = ?, nom = ?, email = ?, poste = ?, telephone = ?, statut_presence = ? WHERE id = ?");
            if ($stmt->execute([$prenom, $nom, $email, $poste, $telephone, $statut_presence, $user_id])) {
                // On met à jour la session avec Prénom + Nom !
                $_SESSION['user_nom'] = trim($prenom . ' ' . $nom);
                $msg = "Vos informations ont été mises à jour.";
                $msgType = "success";
            }
        } else {
            $msg = "Le nom et l'email sont obligatoires.";
            $msgType = "danger";
        }
    }

    // ACTION 2 : Changement de mot de passe
    if (isset($_POST['update_password'])) {
        $old_pwd = $_POST['old_password'];
        $new_pwd = $_POST['new_password'];
        $confirm_pwd = $_POST['confirm_password'];

        $stmt = $db->prepare("SELECT mot_de_passe FROM utilisateurs WHERE id = ?");
        $stmt->execute([$user_id]);
        $current_hash = $stmt->fetchColumn();

        if (password_verify($old_pwd, $current_hash)) {
            if ($new_pwd === $confirm_pwd && strlen($new_pwd) >= 8) {
                $new_hash = password_hash($new_pwd, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE utilisateurs SET mot_de_passe = ? WHERE id = ?");
                $stmt->execute([$new_hash, $user_id]);
                $msg = "Mot de passe modifié avec succès.";
                $msgType = "success";
            } else {
                $msg = "Les mots de passe ne correspondent pas ou sont trop courts (8 caractères minimum).";
                $msgType = "danger";
            }
        } else {
            $msg = "L'ancien mot de passe est incorrect.";
            $msgType = "danger";
        }
    }

    // ACTION 3 : Upload d'Avatar
    if (isset($_POST['update_avatar']) && isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['avatar']['tmp_name'];
        $fileName = $_FILES['avatar']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($fileExtension, $allowedExtensions)) {
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $newFileName = 'avatar_' . $user_id . '_' . time() . '.' . $fileExtension;
            $destPath = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $stmt = $db->prepare("UPDATE utilisateurs SET avatar = ? WHERE id = ?");
                $stmt->execute([$newFileName, $user_id]);
                
                $_SESSION['user_avatar'] = $newFileName;
                
                $msg = "Avatar mis à jour avec succès.";
                $msgType = "success";
            } else {
                $msg = "Erreur lors de la sauvegarde de l'image sur le serveur.";
                $msgType = "danger";
            }
        } else {
            $msg = "Format non autorisé. Utilisez uniquement JPG, PNG ou GIF.";
            $msgType = "danger";
        }
    }
}

// --- RÉCUPÉRATION DES DONNÉES FRAICHES ---
$stmt = $db->prepare("SELECT * FROM utilisateurs WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>

<div class="container-fluid">
    <h2 class="mb-4 fw-bold">Mon Profil</h2>

    <?php if ($msg): ?>
        <div class="alert alert-<?= $msgType ?> alert-dismissible fade show" role="alert">
            <?= $msg ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-xl-4 col-lg-5 mb-4">
            <div class="card shadow-sm border-0 text-center p-4">
                <div class="card-body">
                    <div class="mb-3">
                        <?php if ($user['avatar']): ?>
                            <img src="uploads/avatars/<?= $user['avatar'] ?>" alt="Avatar" class="rounded-circle shadow" style="width: 150px; height: 150px; object-fit: cover; border: 4px solid #fff;">
                        <?php else: ?>
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto shadow" style="width: 150px; height: 150px; font-size: 4rem;">
                                <?= strtoupper(substr($user['nom'], 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <h4 class="fw-bold mb-1"><?= htmlspecialchars(trim(($user['prenom'] ?? '') . ' ' . $user['nom'])) ?></h4>
                    <p class="text-muted mb-4"><?= htmlspecialchars($user['poste'] ?? 'Membre du staff') ?></p>

                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <input class="form-control form-control-sm" type="file" name="avatar" accept="image/*" required>
                        </div>
                        <button type="submit" name="update_avatar" class="btn btn-outline-primary btn-sm w-100 fw-bold">
                            <i class="bi bi-camera me-1"></i> Changer la photo
                        </button>
                    </form>

                    <hr class="my-4">
                    <a href="?page=logout" class="btn btn-danger btn-sm w-100 fw-bold shadow-sm">
                        <i class="bi bi-box-arrow-right me-1"></i> Se déconnecter
                    </a>
                </div>
            </div>
        </div>

        <div class="col-xl-8 col-lg-7">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-primary">Informations personnelles</h5>
                </div>
                <div class="card-body p-4">
                    <form action="" method="POST">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Prénom</label>
                                <input type="text" name="prenom" class="form-control" value="<?= htmlspecialchars($user['prenom'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Nom</label>
                                <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($user['nom']) ?>" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label small fw-bold">Adresse Email</label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Poste / Titre</label>
                                <input type="text" name="poste" class="form-control" placeholder="Ex: Coach, Admin..." value="<?= htmlspecialchars($user['poste'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Téléphone</label>
                                <input type="text" name="telephone" class="form-control" placeholder="06..." value="<?= htmlspecialchars($user['telephone'] ?? '') ?>">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label small fw-bold">Statut de présence (Visible par l'équipe)</label>
                                <select name="statut_presence" class="form-select bg-light">
                                    <option value="online" <?= (($user['statut_presence'] ?? 'online') === 'online') ? 'selected' : '' ?>>🟢 En ligne</option>
                                    <option value="dnd" <?= (($user['statut_presence'] ?? '') === 'dnd') ? 'selected' : '' ?>>🔴 Ne pas déranger</option>
                                    <option value="idle" <?= (($user['statut_presence'] ?? '') === 'idle') ? 'selected' : '' ?>>🟠 Inactif</option>
                                    <option value="offline" <?= (($user['statut_presence'] ?? '') === 'offline') ? 'selected' : '' ?>>⚫ Hors ligne (Invisible)</option>
                                </select>
                                <div class="form-text small">Si vous êtes inactif plus de 5 minutes, votre statut passera automatiquement en "Inactif".</div>
                            </div>
                        </div>

                        <hr class="my-4">
                        <button type="submit" name="update_info" class="btn btn-primary px-4 fw-bold">Enregistrer les modifications</button>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 text-danger border-danger-subtle">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-shield-lock"></i> Sécurité du compte</h5>
                </div>
                <div class="card-body p-4">
                    <form action="" method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Ancien mot de passe</label>
                            <input type="password" name="old_password" class="form-control" required>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Nouveau mot de passe</label>
                                <input type="password" name="new_password" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Confirmer le nouveau mot de passe</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                        </div>
                        <button type="submit" name="update_password" class="btn btn-outline-danger px-4">Mettre à jour le mot de passe</button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>