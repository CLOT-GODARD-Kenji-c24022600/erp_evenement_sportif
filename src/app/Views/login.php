<?php
use App\Controllers\AuthController;

$erreur = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $erreur = AuthController::login($_POST['email'], $_POST['password']);
}
?>

<main class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <section class="card shadow-sm" style="width: 100%; max-width: 400px;">
        <article class="card-body p-4">
            
            <header class="text-center mb-4">
                <h2 class="text-primary">🏆 SportERP</h2>
                <p class="text-muted">Connexion à votre espace</p>
            </header>

            <?php if ($erreur): ?>
                <aside class="alert alert-danger" role="alert">
                    <?= htmlspecialchars($erreur) ?>
                </aside>
            <?php endif; ?>

            <form method="POST" action="?page=login">
                <fieldset class="mb-3 border-0 p-0">
                    <label for="email" class="form-label">Adresse email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </fieldset>
                
                <fieldset class="mb-3 border-0 p-0">
                    <label for="password" class="form-label">Mot de passe</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password" name="password" required>
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">👁️</button>
                    </div>
                </fieldset>

                <div class="d-flex justify-content-end mb-3">
                    <a href="?page=forgot_password" class="text-decoration-none small text-muted">Mot de passe oublié ?</a>
                </div>
                
                <button type="submit" class="btn btn-primary w-100">Se connecter</button>
            </form>

            <footer class="text-center mt-4 border-top pt-3">
                <span class="text-muted small">Pas encore de compte ?</span><br>
                <a href="?page=inscription" class="text-decoration-none fw-bold">Demander un accès</a>
            </footer>

        </article>
    </section>
</main>

<script>
// Petit script pour l'œil du mot de passe
document.getElementById('togglePassword').addEventListener('click', function () {
    const passwordInput = document.getElementById('password');
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        this.textContent = '🙈'; // Cache
    } else {
        passwordInput.type = 'password';
        this.textContent = '👁️'; // Montre
    }
});
</script>