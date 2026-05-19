<?php

/**
 * YES - Your Event Solution
 *
 * Vue : Réinitialisation du mot de passe via token.
 *
 * @file reset_password.php
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.0
 * @since 2026
 *
 * Variables attendues :
 * @var string $message Message de retour.
 * @var string $type    Type du message ('success' ou 'danger').
 * @var string $token   Token de réinitialisation (peut être vide).
 * @var array  $t       Traductions chargées.
 */

declare(strict_types=1);
?>
<main class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <section class="card shadow-sm" style="width: 100%; max-width: 400px;">
        <article class="card-body p-4">

            <header class="text-center mb-4">
                <h1 class="h3 text-primary">🔒 <?= htmlspecialchars($t['auth_reset_title'], ENT_QUOTES) ?></h1>
                <p class="text-muted small"><?= htmlspecialchars($t['auth_reset_hint'], ENT_QUOTES) ?></p>
            </header>

            <?php if ($message !== ''): ?>
                <aside class="alert alert-<?= htmlspecialchars($type, ENT_QUOTES) ?> small" role="alert">
                    <?= htmlspecialchars($message, ENT_QUOTES) ?>
                </aside>
            <?php endif; ?>

            <?php if ($type !== 'success' && $token !== ''): ?>
            <form method="POST" action="reset_password?token=<?= htmlspecialchars($token, ENT_QUOTES) ?>" novalidate>
                <fieldset class="mb-3 border-0 p-0">
                    <label for="password" class="form-label">
                        <?= htmlspecialchars($t['profile_field_new_pwd'], ENT_QUOTES) ?>
                    </label>
                    <input type="password" class="form-control" id="password" name="password"
                           autocomplete="new-password" required minlength="8">
                </fieldset>
                <fieldset class="mb-3 border-0 p-0">
                    <label for="confirm_password" class="form-label">
                        <?= htmlspecialchars($t['profile_field_confirm'], ENT_QUOTES) ?>
                    </label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                           autocomplete="new-password" required minlength="8">
                </fieldset>
                <button type="submit" class="btn btn-primary w-100">
                    <?= htmlspecialchars($t['auth_reset_btn'], ENT_QUOTES) ?>
                </button>
            </form>
            <?php endif; ?>

            <?php if ($type === 'success' || $token === ''): ?>
            <footer class="text-center mt-4">
                <a href="login" class="btn btn-outline-primary w-100">
                    <?= htmlspecialchars($t['auth_go_login'], ENT_QUOTES) ?>
                </a>
            </footer>
            <?php endif; ?>

        </article>
    </section>
</main>