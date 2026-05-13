<?php

/**
 * YES - Your Event Solution
 *
 * Vue : Inscription d'un nouvel utilisateur.
 *
 * @file inscription.php
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.0
 * @since 2026
 *
 * Variables attendues :
 * @var string $message Message de retour (vide si aucun).
 * @var string $type    Type du message ('success' ou 'danger').
 * @var array  $t       Traductions chargées.
 */

declare(strict_types=1);
?>
<main class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <section class="card shadow-sm register-card">
        <article class="card-body p-4">

            <header class="text-center mb-4">
                <h1 class="h3 text-primary">📝 <?= htmlspecialchars($t['auth_register_title'], ENT_QUOTES) ?></h1>
                <p class="text-muted"><?= htmlspecialchars($t['app_name'], ENT_QUOTES) ?></p>
            </header>

            <?php if ($message !== ''): ?>
                <aside class="alert alert-<?= htmlspecialchars($type, ENT_QUOTES) ?>" role="alert">
                    <?= htmlspecialchars($message, ENT_QUOTES) ?>
                </aside>
            <?php endif; ?>

            <?php if ($type !== 'success'): ?>
            <form method="POST" action="inscription" novalidate>
                <section class="row">
                    <fieldset class="col-md-6 mb-3 border-0 p-0 px-2">
                        <label for="prenom" class="form-label">
                            <?= htmlspecialchars($t['profile_field_prenom'], ENT_QUOTES) ?>
                        </label>
                        <input type="text" class="form-control" id="prenom" name="prenom"
                               autocomplete="given-name" required>
                    </fieldset>
                    <fieldset class="col-md-6 mb-3 border-0 p-0 px-2">
                        <label for="nom" class="form-label">
                            <?= htmlspecialchars($t['profile_field_nom'], ENT_QUOTES) ?>
                        </label>
                        <input type="text" class="form-control" id="nom" name="nom"
                               autocomplete="family-name" required>
                    </fieldset>
                </section>

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
                    <input type="password" class="form-control" id="password" name="password"
                           autocomplete="new-password" required minlength="8">
                </fieldset>

                <fieldset class="mb-3 border-0 p-0">
                    <label for="confirm_password" class="form-label">
                        <?= htmlspecialchars($t['profile_field_confirm'], ENT_QUOTES) ?>
                    </label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                           autocomplete="new-password" required>
                </fieldset>

                <button type="submit" class="btn btn-primary w-100 mt-2">
                    <?= htmlspecialchars($t['auth_register_btn'], ENT_QUOTES) ?>
                </button>
            </form>
            <?php endif; ?>

            <footer class="text-center mt-4 border-top pt-3">
                <a href="login" class="text-decoration-none">
                    🔙 <?= htmlspecialchars($t['auth_register_back'], ENT_QUOTES) ?>
                </a>
            </footer>

        </article>
    </section>
</main>