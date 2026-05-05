<?php
use App\Controllers\AuthController;

$message = '';
$type = 'danger';
$token = $_GET['token'] ?? '';

// Si l'utilisateur arrive sans token dans l'URL
if (empty($token)) {
    $message = "Aucun jeton de sécurité fourni.";
} 
// Si le formulaire de nouveau mot de passe est soumis
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = AuthController::resetPassword($token, $_POST['password'], $_POST['confirm_password']);
    $message = $result['message'];
    $type = $result['status'] === 'success' ? 'success' : 'danger';
}
?>

<main class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <section class="card shadow-sm" style="width: 100%; max-width: 400px;">
        <article class="card-body p-4">
            <header class="text-center mb-4">
                <h2 class="text-primary">🔒 Nouveau mot de passe</h2>
                <p class="text-muted small">Choisissez un nouveau mot de passe sécurisé.</p>
            </header>

            <?php if ($message): ?>
                <aside class="alert alert-<?= $type ?> small"><?= htmlspecialchars($message) ?></aside>
            <?php endif; ?>

            <?php if ($type !== 'success' && !empty($token)): ?>
            <form method="POST">
                <fieldset class="mb-3 border-0 p-0">
                    <label for="password" class="form-label">Nouveau mot de passe</label>
                    <input type="password" class="form-control" id="password" name="password" required minlength="8">
                </fieldset>
                
                <fieldset class="mb-3 border-0 p-0">
                    <label for="confirm_password" class="form-label">Confirmez le mot de passe</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="8">
                </fieldset>

                <button type="submit" class="btn btn-primary w-100">Enregistrer</button>
            </form>
            <?php endif; ?>

            <?php if ($type === 'success' || empty($token)): ?>
                <footer class="text-center mt-4">
                    <a href="?page=login" class="btn btn-outline-primary w-100">Aller à la connexion</a>
                </footer>
            <?php endif; ?>

        </article>
    </section>
</main>