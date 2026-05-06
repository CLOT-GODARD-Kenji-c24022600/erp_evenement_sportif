<?php
use App\Controllers\AuthController;

$message = '';
$type = 'danger';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = AuthController::requestReset($_POST['email']);
    $message = $result['message'];
    $type = $result['status'] === 'success' ? 'success' : 'danger';
}
?>

<main class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <section class="card shadow-sm" style="width: 100%; max-width: 400px;">
        <article class="card-body p-4">
            <header class="text-center mb-4">
                <h2 class="text-primary">🔑 Mot de passe oublié</h2>
                <p class="text-muted small">Entrez votre email pour recevoir un lien de réinitialisation.</p>
            </header>

            <?php if ($message): ?>
                <aside class="alert alert-<?= $type ?> small"><?= htmlspecialchars($message) ?></aside>
            <?php endif; ?>

            <?php if ($type !== 'success'): ?>
            <form method="POST">
                <fieldset class="mb-3 border-0 p-0">
                    <label for="email" class="form-label">Votre adresse email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </fieldset>
                <button type="submit" class="btn btn-primary w-100">Envoyer le lien</button>
            </form>
            <?php endif; ?>

            <footer class="text-center mt-4 border-top pt-3">
                <a href="?page=login" class="text-decoration-none small">🔙 Retour à la connexion</a>
            </footer>
        </article>
    </section>
</main>