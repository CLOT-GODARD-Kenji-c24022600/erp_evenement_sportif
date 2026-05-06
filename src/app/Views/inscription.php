<?php
use App\Controllers\AuthController;

$message = '';
$type = ''; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = AuthController::register($_POST['nom'], $_POST['prenom'], $_POST['email'], $_POST['password'], $_POST['confirm_password']);
    $message = $result['message'];
    $type = $result['status'] === 'success' ? 'success' : 'danger';
}
?>

<main class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <section class="card shadow-sm" style="width: 100%; max-width: 500px;">
        <article class="card-body p-4">
            
            <header class="text-center mb-4">
                <h2 class="text-primary">📝 Demande d'accès</h2>
                <p class="text-muted">Créez votre compte SportERP</p>
            </header>

            <?php if ($message): ?>
                <aside class="alert alert-<?= $type ?>" role="alert">
                    <?= htmlspecialchars($message) ?>
                </aside>
            <?php endif; ?>

            <?php if ($type !== 'success'): // On cache le formulaire si l'inscription a réussi ?>
            <form method="POST" action="?page=inscription">
                <div class="row">
                    <fieldset class="col-md-6 mb-3 border-0 p-0 px-2">
                        <label for="prenom" class="form-label">Prénom</label>
                        <input type="text" class="form-control" id="prenom" name="prenom" required>
                    </fieldset>
                    <fieldset class="col-md-6 mb-3 border-0 p-0 px-2">
                        <label for="nom" class="form-label">Nom</label>
                        <input type="text" class="form-control" id="nom" name="nom" required>
                    </fieldset>
                </div>
                
                <fieldset class="mb-3 border-0 p-0">
                    <label for="email" class="form-label">Adresse email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </fieldset>
                
                <fieldset class="mb-3 border-0 p-0">
                    <label for="password" class="form-label">Mot de passe</label>
                    <input type="password" class="form-control" id="password" name="password" required minlength="8">
                </fieldset>

                <fieldset class="mb-3 border-0 p-0">
                    <label for="confirm_password" class="form-label">Confirmer le mot de passe</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </fieldset>
                
                <button type="submit" class="btn btn-primary w-100 mt-2">Envoyer la demande</button>
            </form>
            <?php endif; ?>

            <footer class="text-center mt-4 border-top pt-3">
                <a href="?page=login" class="text-decoration-none">🔙 Retour à la connexion</a>
            </footer>

        </article>
    </section>
</main>