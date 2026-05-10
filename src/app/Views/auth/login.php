<?php

/**
 * YES - Your Event Solution
 *
 * Vue : Connexion utilisateur.
 *
 * @file login.php
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.0
 * @since 2026
 *
 * Variables attendues :
 * @var string $erreur Message d'erreur (vide si aucun).
 * @var array  $t      Traductions chargées.
 */

declare(strict_types=1);
?>
<main class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <section class="card shadow-sm login-card">
        <article class="card-body p-4">

            <header class="text-center mb-4">
                <h1 class="h3 text-primary">🏆 <?= htmlspecialchars($t['app_name'], ENT_QUOTES) ?></h1>
                <p class="text-muted"><?= htmlspecialchars($t['auth_login_title'], ENT_QUOTES) ?></p>
            </header>

            <?php if ($erreur !== ''): ?>
                <aside class="alert alert-danger" role="alert">
                    <?= htmlspecialchars($erreur, ENT_QUOTES) ?>
                </aside>
            <?php endif; ?>

            <form method="POST" action="?page=login" novalidate>
                <fieldset class="mb-3 border-0 p-0">
                    <label for="email" class="form-label">
                        <?= htmlspecialchars($t['auth_login_email'], ENT_QUOTES) ?>
                    </label>
                    <input type="email" class="form-control" id="email" name="email"
                           autocomplete="email" required>
                </fieldset>

                <fieldset class="mb-3 border-0 p-0">
                    <label for="password" class="form-label">
                        <?= htmlspecialchars($t['auth_login_pwd'], ENT_QUOTES) ?>
                    </label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password" name="password"
                               autocomplete="current-password" required>
                        <button class="btn btn-outline-secondary" type="button"
                                id="togglePassword"
                                aria-label="Afficher ou masquer le mot de passe">👁️</button>
                    </div>
                </fieldset>

                <nav class="d-flex justify-content-end mb-3">
                    <a href="?page=forgot_password" class="text-decoration-none small text-muted">
                        <?= htmlspecialchars($t['auth_login_forgot'], ENT_QUOTES) ?>
                    </a>
                </nav>

                <button type="submit" class="btn btn-primary w-100">
                    <?= htmlspecialchars($t['auth_login_btn'], ENT_QUOTES) ?>
                </button>
            </form>

            <footer class="text-center mt-4 border-top pt-3">
                <span class="text-muted small">
                    <?= htmlspecialchars($t['auth_login_no_account'], ENT_QUOTES) ?>
                </span><br>
                <a href="?page=inscription" class="text-decoration-none fw-bold">
                    <?= htmlspecialchars($t['auth_login_register'], ENT_QUOTES) ?>
                </a>
            </footer>

        </article>
    </section>
</main>