<?php

/**
 * YES - Your Event Solution
 *
 * Vue : Mot de passe oublié.
 *
 * @file forgot_password.php
 * @author CELESTINE Samuel
 * @author CLOT-GODARD Kenji
 * @version 1.0
 * @since 2026
 *
 * Variables attendues :
 * @var string $message Message de retour.
 * @var string $type    Type du message ('success' ou 'danger').
 * @var array  $t       Traductions chargées.
 */

declare(strict_types=1);
?>
<main class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <section class="card shadow-sm" style="width: 100%; max-width: 400px;">
        <article class="card-body p-4">

            <header class="text-center mb-4">
                <h1 class="h3 text-primary">🔑 <?= htmlspecialchars($t['auth_forgot_title'], ENT_QUOTES) ?></h1>
                <p class="text-muted small"><?= htmlspecialchars($t['auth_forgot_hint'], ENT_QUOTES) ?></p>
            </header>

            <?php if ($message !== ''): ?>
                <aside class="alert alert-<?= htmlspecialchars($type, ENT_QUOTES) ?> small" role="alert">
                    <?= htmlspecialchars($message, ENT_QUOTES) ?>
                </aside>
            <?php endif; ?>

            <?php if ($type !== 'success'): ?>
            <form method="POST" action="forgot_password" novalidate>
                <fieldset class="mb-3 border-0 p-0">
                    <label for="email" class="form-label">
                        <?= htmlspecialchars($t['auth_login_email'], ENT_QUOTES) ?>
                    </label>
                    <input type="email" class="form-control" id="email" name="email"
                           autocomplete="email" required>
                </fieldset>
                <button type="submit" class="btn btn-primary w-100">
                    <?= htmlspecialchars($t['auth_forgot_btn'], ENT_QUOTES) ?>
                </button>
            </form>
            <?php endif; ?>

            <footer class="text-center mt-4 border-top pt-3">
                <a href="login" class="text-decoration-none small">
                    🔙 <?= htmlspecialchars($t['auth_register_back'], ENT_QUOTES) ?>
                </a>
            </footer>

        </article>
    </section>
</main>